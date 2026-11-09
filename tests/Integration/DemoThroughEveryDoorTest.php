<?php

declare(strict_types=1);

namespace Talis\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Talis\Tests\Integration\Support\DemoServer;

/**
 * Walks the demo user resource CRUD through every door the library ships:
 * Rest, HTTP, Cli, CliEmbedded and Daemon/SpoolWorker. Each test gets its
 * own temp var folder, so the doors never share data or a log file.
 *
 * A failure here points at Tasks 8-11 (the doors or the demo app), not at
 * this test file: fix the bug in place and pin it with a unit test.
 *
 * @phpstan-type Envelope array{status: string, type: string, message: string, payload: mixed}
 * @phpstan-type User array{id: int, name: string, email: string}
 */
final class DemoThroughEveryDoorTest extends TestCase
{
    private const string UNICODE_NAME = 'Ünal Şahin';

    private string $repoRoot = '';

    /**
     * @var list<DemoServer>
     */
    private array $servers = [];

    /**
     * @var list<string>
     */
    private array $tempDirs = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->repoRoot = dirname(__DIR__, 2);
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->servers as $server) {
            $server->stop();
        }
        $this->servers = [];

        foreach ($this->tempDirs as $dir) {
            $this->removeTree($dir);
        }
        $this->tempDirs = [];
    }

    public function testRestCrud(): void
    {
        $server = $this->startServer();
        $base = $server->baseUrl() . '/api';

        $created = $this->httpJson('POST', "{$base}/demo/user", ['name' => self::UNICODE_NAME, 'email' => 'unal@example.com']);
        self::assertSame(201, $created['status']);
        $user = $this->asUser($created['body']['payload']);
        self::assertSame(self::UNICODE_NAME, $user['name']);
        self::assertSame('unal@example.com', $user['email']);
        $id = $user['id'];

        $one = $this->httpJson('GET', "{$base}/demo/user/id/{$id}");
        self::assertSame(200, $one['status']);
        $oneUser = $this->asUser($one['body']['payload']);
        self::assertSame(self::UNICODE_NAME, $oneUser['name']);
        self::assertSame($id, $oneUser['id']);

        $list = $this->httpJson('GET', "{$base}/demo/user");
        self::assertSame(200, $list['status']);
        $users = $this->asUserList($list['body']['payload']);
        self::assertCount(1, $users);
        self::assertSame(self::UNICODE_NAME, $users[0]['name']);

        // 204 No Content: the server sends no body, so check with a follow-up GET.
        $updated = $this->httpRaw('PUT', "{$base}/demo/user/id/{$id}", ['name' => 'Ann Smith']);
        self::assertSame(204, $updated['status']);
        self::assertSame('', $updated['raw']);
        $afterUpdate = $this->httpJson('GET', "{$base}/demo/user/id/{$id}");
        $afterUser = $this->asUser($afterUpdate['body']['payload']);
        self::assertSame('Ann Smith', $afterUser['name']);

        $deleted = $this->httpRaw('DELETE', "{$base}/demo/user/id/{$id}");
        self::assertSame(204, $deleted['status']);
        self::assertSame('', $deleted['raw']);

        $gone = $this->httpJson('GET', "{$base}/demo/user/id/{$id}");
        self::assertSame(404, $gone['status']);
    }

    public function testHttpCrud(): void
    {
        $server = $this->startServer();
        $base = $server->baseUrl() . '/http';

        $created = $this->httpJson('POST', "{$base}/demo/user/create", ['name' => self::UNICODE_NAME, 'email' => 'unal@example.com']);
        self::assertSame(201, $created['status']);
        $user = $this->asUser($created['body']['payload']);
        self::assertSame(self::UNICODE_NAME, $user['name']);
        $id = $user['id'];

        $one = $this->httpJson('GET', "{$base}/demo/user/read/id/{$id}");
        self::assertSame(200, $one['status']);
        $oneUser = $this->asUser($one['body']['payload']);
        self::assertSame(self::UNICODE_NAME, $oneUser['name']);

        $list = $this->httpJson('GET', "{$base}/demo/user/read");
        self::assertSame(200, $list['status']);
        $users = $this->asUserList($list['body']['payload']);
        self::assertCount(1, $users);

        $updated = $this->httpRaw('PUT', "{$base}/demo/user/update/id/{$id}", ['name' => 'Ann Smith']);
        self::assertSame(204, $updated['status']);
        $afterUpdate = $this->httpJson('GET', "{$base}/demo/user/read/id/{$id}");
        $afterUser = $this->asUser($afterUpdate['body']['payload']);
        self::assertSame('Ann Smith', $afterUser['name']);

        $deleted = $this->httpRaw('DELETE', "{$base}/demo/user/delete/id/{$id}");
        self::assertSame(204, $deleted['status']);

        $gone = $this->httpJson('GET', "{$base}/demo/user/read/id/{$id}");
        self::assertSame(404, $gone['status']);
    }

    public function testCliCrud(): void
    {
        $varDir = $this->freshTempDir('cli');

        $created = $this->runLordCommander($varDir, '/demo/user/create', ['name' => self::UNICODE_NAME, 'email' => 'unal@example.com']);
        self::assertSame(0, $created['exitCode']);
        self::assertSame('201 Resource Created', $created['body']['status']);
        $user = $this->asUser($created['body']['payload']);
        $id = $user['id'];
        self::assertSame(self::UNICODE_NAME, $user['name']);

        $read = $this->runLordCommander($varDir, "/demo/user/read/id/{$id}");
        self::assertSame(0, $read['exitCode']);
        $readUser = $this->asUser($read['body']['payload']);
        self::assertSame(self::UNICODE_NAME, $readUser['name']);

        $deleted = $this->runLordCommander($varDir, "/demo/user/delete/id/{$id}");
        self::assertSame(0, $deleted['exitCode']);

        $gone = $this->runLordCommander($varDir, "/demo/user/read/id/{$id}");
        self::assertSame(1, $gone['exitCode']);
        self::assertStringContainsString('not found', $gone['body']['message']);
    }

    public function testCliEmbeddedCrud(): void
    {
        // The demo bootstrap defines DEMO_VAR once per process. Point it at
        // a fresh temp folder before the first require, exactly like
        // tests/Unit/Demo/UserApiTest.php does. Requiring it also sets
        // TalisMain::$appPath, the logger and $registeredInitFunc.
        if (!defined('DEMO_VAR')) {
            putenv('TALIS_DEMO_VAR=' . $this->freshTempDir('embedded'));
        }
        require_once $this->repoRoot . '/demo/config/bootstrap.php';
        putenv('TALIS_DEMO_VAR');

        if (!str_starts_with(DEMO_VAR, sys_get_temp_dir())) {
            self::markTestSkipped('DEMO_VAR does not point at a temp folder; not touching its data.');
        }

        $savedAppPath = \Talis\TalisMain::$appPath;
        $savedLogger = \Talis\TalisMain::logger();
        $savedInitFunc = \Talis\TalisMain::$registeredInitFunc;
        try {
            \Talis\TalisMain::$appPath = $this->repoRoot . '/demo/application';
            \Talis\TalisMain::setLogger(new \Demo\DemoLogger(DEMO_VAR . '/log/demo.log'));
            \Talis\TalisMain::$registeredInitFunc = static function (\Talis\Message\Request $Request): void {
                \Talis\TalisMain::$context->set('userStore', new \Demo\User\UserStore(DEMO_VAR . '/data/users.json'));
            };

            $emitter = new \Talis\Tests\Support\SpyEmitter();
            $door = new \Talis\Doors\CliEmbedded($emitter);

            $created = $door->gogogo('/demo/user/create', (object) ['params' => (object) ['name' => self::UNICODE_NAME, 'email' => 'unal@example.com']]);
            self::assertSame(201, $created->getStatus()->getCode());
            $user = $this->asUser($created->getPayload());
            $id = $user['id'];
            self::assertSame(self::UNICODE_NAME, $user['name']);

            $read = $door->gogogo("/demo/user/read/id/{$id}", new \stdClass());
            self::assertSame(200, $read->getStatus()->getCode());

            $deleted = $door->gogogo("/demo/user/delete/id/{$id}", new \stdClass());
            self::assertSame(204, $deleted->getStatus()->getCode());

            $gone = $door->gogogo("/demo/user/read/id/{$id}", new \stdClass());
            self::assertSame(404, $gone->getStatus()->getCode());
            self::assertSame($gone, $emitter->last());
        } finally {
            \Talis\TalisMain::$appPath = $savedAppPath;
            \Talis\TalisMain::setLogger($savedLogger);
            \Talis\TalisMain::$registeredInitFunc = $savedInitFunc;
        }
    }

    public function testDaemonCrud(): void
    {
        $varDir = $this->freshTempDir('daemon');
        $spool = $varDir . '/spool';
        mkdir($spool . '/incoming', 0775, true);

        $this->dropSpoolMessage($spool, '1.json', ['url' => '/demo/user/create', 'params' => ['name' => self::UNICODE_NAME, 'email' => 'unal@example.com']]);

        $firstPass = $this->runWorkerOnce($varDir);
        self::assertSame(0, $firstPass['exitCode']);
        self::assertSame("Handled 1 file(s)\n", $firstPass['stdout']);
        self::assertFileExists($spool . '/done/1.json');
        self::assertFileDoesNotExist($spool . '/incoming/1.json');

        $oneUser = $this->asUser($this->userDataFile($varDir)['1'] ?? null);
        self::assertSame(self::UNICODE_NAME, $oneUser['name']);

        $this->dropSpoolMessage($spool, '2.json', ['url' => '/demo/user/delete/id/1', 'params' => []]);

        $secondPass = $this->runWorkerOnce($varDir);
        self::assertSame(0, $secondPass['exitCode']);
        self::assertSame("Handled 1 file(s)\n", $secondPass['stdout']);
        self::assertFileExists($spool . '/done/2.json');

        self::assertSame([], $this->userDataFile($varDir));
    }

    public function testErrorsAcrossDoors(): void
    {
        $server = $this->startServer();
        $base = $server->baseUrl() . '/api';

        $missingField = $this->httpJson('POST', "{$base}/demo/user", ['name' => 'Ann']);
        self::assertSame(400, $missingField['status']);

        $badEmail = $this->httpJson('POST', "{$base}/demo/user", ['name' => 'Ann', 'email' => 'not-an-email']);
        self::assertSame(422, $badEmail['status']);

        $unknownId = $this->httpJson('GET', "{$base}/demo/user/id/999999");
        self::assertSame(404, $unknownId['status']);

        $badRoute = $this->httpJson('GET', "{$server->baseUrl()}/api/demo/nosuchaction");
        self::assertSame(404, $badRoute['status']);

        $cliVarDir = $this->freshTempDir('cli-errors');

        $missingFieldCli = $this->runLordCommander($cliVarDir, '/demo/user/create', ['name' => 'Ann']);
        self::assertSame(1, $missingFieldCli['exitCode']);
        self::assertSame('400 Bad Request', $missingFieldCli['body']['status']);

        $badEmailCli = $this->runLordCommander($cliVarDir, '/demo/user/create', ['name' => 'Ann', 'email' => 'not-an-email']);
        self::assertSame(1, $badEmailCli['exitCode']);
        self::assertSame('422 Unprocessable Entity', $badEmailCli['body']['status']);

        $unknownIdCli = $this->runLordCommander($cliVarDir, '/demo/user/read/id/999999');
        self::assertSame(1, $unknownIdCli['exitCode']);
        self::assertSame('404 Not Found', $unknownIdCli['body']['status']);

        $badRouteCli = $this->runLordCommander($cliVarDir, '/demo/nosuchaction');
        self::assertSame(1, $badRouteCli['exitCode']);
        self::assertSame('404 Not Found', $badRouteCli['body']['status']);

        $daemonVarDir = $this->freshTempDir('daemon-errors');
        $spool = $daemonVarDir . '/spool';
        mkdir($spool . '/incoming', 0775, true);

        $this->dropSpoolMessage($spool, 'missing-field.json', ['url' => '/demo/user/create', 'params' => ['name' => 'Ann']]);
        $this->dropSpoolMessage($spool, 'bad-email.json', ['url' => '/demo/user/create', 'params' => ['name' => 'Ann', 'email' => 'not-an-email']]);
        $this->dropSpoolMessage($spool, 'unknown-id.json', ['url' => '/demo/user/read/id/999999', 'params' => []]);
        $this->dropSpoolMessage($spool, 'bad-route.json', ['url' => '/demo/nosuchaction', 'params' => []]);

        $pass = $this->runWorkerOnce($daemonVarDir);
        self::assertSame(0, $pass['exitCode']);
        self::assertSame("Handled 4 file(s)\n", $pass['stdout']);

        foreach (['missing-field.json', 'bad-email.json', 'unknown-id.json', 'bad-route.json'] as $name) {
            self::assertFileExists("{$spool}/failed/{$name}", "{$name} should have failed");
            self::assertFileDoesNotExist("{$spool}/done/{$name}");
        }
    }

    public function testOptionsOverRest(): void
    {
        $server = $this->startServer();
        $base = $server->baseUrl() . '/api';

        $response = $this->httpHeadersOnly('OPTIONS', "{$base}/demo/user");

        self::assertSame(204, $response['status']);
        self::assertContains('Allow: OPTIONS,GET,POST,PUT,DELETE', $response['headers']);
    }

    private function startServer(): DemoServer
    {
        $server = new DemoServer();
        $server->start();
        $this->servers[] = $server;
        return $server;
    }

    /**
     * @return User
     */
    private function asUser(mixed $payload): array
    {
        self::assertIsArray($payload);
        $id = $payload['id'] ?? null;
        $name = $payload['name'] ?? null;
        $email = $payload['email'] ?? null;
        self::assertIsInt($id);
        self::assertIsString($name);
        self::assertIsString($email);
        return ['id' => $id, 'name' => $name, 'email' => $email];
    }

    /**
     * @return list<User>
     */
    private function asUserList(mixed $payload): array
    {
        self::assertIsArray($payload);
        $users = $payload['users'] ?? null;
        self::assertIsArray($users);
        return array_values(array_map($this->asUser(...), $users));
    }

    /**
     * The decoded users.json "users" map (id => user), as the JSON holds it:
     * string keys, not necessarily a list.
     *
     * @return array<array-key, mixed>
     */
    private function userDataFile(string $varDir): array
    {
        $raw = (string) file_get_contents($varDir . '/data/users.json');
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);
        $users = $data['users'] ?? null;
        self::assertIsArray($users);
        return $users;
    }

    /**
     * @param non-empty-string $method
     * @param array<string, mixed>|null $params body->params, or null for no body
     * @return array{status: int, body: Envelope}
     */
    private function httpJson(string $method, string $url, ?array $params = null): array
    {
        $raw = $this->httpRaw($method, $url, $params);
        return ['status' => $raw['status'], 'body' => $this->decodeEnvelope($raw['raw'])];
    }

    /**
     * @param non-empty-string $method
     * @param array<string, mixed>|null $params body->params, or null for no body
     * @return array{status: int, raw: string}
     */
    private function httpRaw(string $method, string $url, ?array $params = null): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException("Could not init curl for [{$url}]");
        }

        $headers = [];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($params !== null) {
            $json = json_encode(['params' => $params], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new \RuntimeException("curl request to [{$url}] failed: " . curl_error($ch));
        }
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        self::assertIsString($raw);

        return ['status' => (int) $status, 'raw' => $raw];
    }

    /**
     * Writes incoming/$name directly: no producer in this app writes
     * .json.tmp then renames, so there is nothing else racing the write.
     *
     * @param array<string, mixed> $message
     */
    private function dropSpoolMessage(string $spoolDir, string $name, array $message): void
    {
        $json = json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        file_put_contents("{$spoolDir}/incoming/{$name}", $json);
    }

    /**
     * @return array{exitCode: int, stdout: string}
     */
    private function runWorkerOnce(string $varDir): array
    {
        $process = $this->runProcess([PHP_BINARY, "{$this->repoRoot}/demo/doors/daemon/worker.php", '--once'], $varDir);
        return ['exitCode' => $process['exitCode'], 'stdout' => $process['stdout']];
    }

    /**
     * Runs demo/doors/cli/lord_commander through proc_open, with TALIS_DEMO_VAR
     * set to $varDir. Parses the JSON that comes before the "FIN with [...]" line.
     *
     * @param array<string, mixed>|null $params body->params, or null for no body
     * @return array{exitCode: int, body: Envelope}
     */
    private function runLordCommander(string $varDir, string $url, ?array $params = null): array
    {
        $command = [PHP_BINARY, "{$this->repoRoot}/demo/doors/cli/lord_commander", $url];
        if ($params !== null) {
            $command[] = json_encode(['params' => $params], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        $process = $this->runProcess($command, $varDir);

        if (preg_match('/^(.*)\nFIN with \[\d+\]\n$/s', $process['stdout'], $matches) !== 1) {
            throw new \RuntimeException("lord_commander output did not end with a FIN line: [{$process['stdout']}]");
        }

        return ['exitCode' => $process['exitCode'], 'body' => $this->decodeEnvelope($matches[1])];
    }

    /**
     * @return Envelope
     */
    private function decodeEnvelope(string $raw): array
    {
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        $status = $decoded['status'] ?? null;
        $type = $decoded['type'] ?? null;
        $message = $decoded['message'] ?? null;
        self::assertIsString($status);
        self::assertIsString($type);
        self::assertIsString($message);

        return ['status' => $status, 'type' => $type, 'message' => $message, 'payload' => $decoded['payload'] ?? null];
    }

    /**
     * @param list<string> $command
     * @return array{exitCode: int, stdout: string, stderr: string}
     */
    private function runProcess(array $command, string $varDir): array
    {
        putenv("TALIS_DEMO_VAR={$varDir}");
        $process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->repoRoot,
        );
        putenv('TALIS_DEMO_VAR');

        if ($process === false) {
            throw new \RuntimeException('Could not start process: ' . implode(' ', $command));
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        self::assertIsString($stdout);
        self::assertIsString($stderr);

        return ['exitCode' => $exitCode, 'stdout' => $stdout, 'stderr' => $stderr];
    }

    /**
     * @param non-empty-string $method
     * @return array{status: int, headers: list<string>}
     */
    private function httpHeadersOnly(string $method, string $url): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException("Could not init curl for [{$url}]");
        }

        /** @var list<string> $headerLines */
        $headerLines = [];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, static function (\CurlHandle $handle, string $line) use (&$headerLines): int {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $headerLines[] = $trimmed;
            }
            return strlen($line);
        });

        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new \RuntimeException("curl request to [{$url}] failed: " . curl_error($ch));
        }
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        return ['status' => (int) $status, 'headers' => $headerLines];
    }

    private function freshTempDir(string $label): string
    {
        $dir = sys_get_temp_dir() . "/talis-integration-{$label}-" . bin2hex(random_bytes(6));
        $this->tempDirs[] = $dir;
        return $dir;
    }

    private function removeTree(string $path): void
    {
        if (is_file($path) || is_link($path)) {
            unlink($path);
            return;
        }
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) ?: [] as $name) {
            if ($name !== '.' && $name !== '..') {
                $this->removeTree($path . '/' . $name);
            }
        }
        rmdir($path);
    }
}

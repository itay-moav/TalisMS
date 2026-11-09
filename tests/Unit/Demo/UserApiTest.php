<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Demo;

use PHPUnit\Framework\TestCase;
use Talis\commons\iLogger;
use Talis\commons\NullLogger;
use Talis\Doors\CliEmbedded;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Router\DefaultRouter;
use Talis\TalisMain;
use Talis\Tests\Support\SpyEmitter;

/**
 * Runs the demo API classes in this process, through the CliEmbedded door,
 * with the demo bootstrap pointed at a temp var folder.
 */
final class UserApiTest extends TestCase
{
    private static ?string $tempVar = null;

    private static string $appPath = '';

    private static ?iLogger $logger = null;

    private static ?\Closure $initFunc = null;

    /**
     * @var array<array-key, mixed>
     */
    private array $savedGet = [];

    /**
     * @var array<array-key, mixed>
     */
    private array $savedPost = [];

    private SpyEmitter $emitter;

    public static function setUpBeforeClass(): void
    {
        if (!defined('DEMO_VAR')) {
            self::$tempVar = sys_get_temp_dir() . '/talis-demo-api-' . bin2hex(random_bytes(6));
            putenv('TALIS_DEMO_VAR=' . self::$tempVar);
        }
        require_once __DIR__ . '/../../../demo/config/bootstrap.php';

        // Keep what the bootstrap set; other tests reset these statics.
        self::$appPath = TalisMain::$appPath;
        self::$logger = TalisMain::logger();
        self::$initFunc = TalisMain::$registeredInitFunc;
    }

    public static function tearDownAfterClass(): void
    {
        putenv('TALIS_DEMO_VAR');
        if (self::$tempVar !== null) {
            self::removeTree(self::$tempVar);
        }
    }

    protected function setUp(): void
    {
        $var = self::demoVar();
        if (!str_starts_with($var, sys_get_temp_dir())) {
            self::markTestSkipped('DEMO_VAR does not point at a temp folder; not touching its data.');
        }
        if (is_file($var . '/data/users.json')) {
            unlink($var . '/data/users.json');
        }

        $this->savedGet = $_GET;
        $this->savedPost = $_POST;
        $_GET = [];
        $_POST = [];

        TalisMain::$appPath = self::$appPath;
        TalisMain::setLogger(self::$logger ?? new NullLogger());
        TalisMain::$registeredInitFunc = self::$initFunc;
        TalisMain::$registeredRouter = DefaultRouter::class;

        $this->emitter = new SpyEmitter();
    }

    protected function tearDown(): void
    {
        $_GET = $this->savedGet;
        $_POST = $this->savedPost;
        TalisMain::$appPath = '';
        TalisMain::setLogger(new NullLogger());
        TalisMain::$registeredInitFunc = null;
    }

    private static function demoVar(): string
    {
        return DEMO_VAR;
    }

    private static function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            self::assertInstanceOf(\SplFileInfo::class, $item);
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    /**
     * @param array<string, mixed>|null $params body params, or null for no body
     */
    private function call(string $url, ?array $params = null): Response
    {
        $body = new \stdClass();
        if ($params !== null) {
            $body->params = (object) $params;
        }
        return (new CliEmbedded($this->emitter))->gogogo($url, $body);
    }

    /**
     * @return array{id: int, name: string, email: string}
     */
    private function createUser(string $name, string $email): array
    {
        $payload = $this->call('/demo/user/create', ['name' => $name, 'email' => $email])->getPayload();
        self::assertIsArray($payload);
        self::assertIsInt($payload['id'] ?? null);
        self::assertIsString($payload['name'] ?? null);
        self::assertIsString($payload['email'] ?? null);
        return ['id' => $payload['id'], 'name' => $payload['name'], 'email' => $payload['email']];
    }

    private static function assertStatus(int $code, Response $Response): void
    {
        self::assertSame($code, $Response->getStatus()->getCode(), $Response->getMessage());
    }

    public function testCreateReturns201WithUser(): void
    {
        $Response = $this->call('/demo/user/create', ['name' => 'Ann', 'email' => 'ann@example.com']);

        self::assertStatus(201, $Response);
        self::assertSame(ResponseType::Response, $Response->getResponseType());
        self::assertSame(['id' => 1, 'name' => 'Ann', 'email' => 'ann@example.com'], $Response->getPayload());
        self::assertSame($Response, $this->emitter->last());
        self::assertFileExists(self::demoVar() . '/data/users.json');
        self::assertFileExists(self::demoVar() . '/log/demo.log');
    }

    public function testCreateTrimsName(): void
    {
        $Response = $this->call('/demo/user/create', ['name' => "  Ann Lee \n", 'email' => 'ann@example.com']);

        self::assertStatus(201, $Response);
        self::assertSame(['id' => 1, 'name' => 'Ann Lee', 'email' => 'ann@example.com'], $Response->getPayload());
    }

    public function testCreateBlankNameIs400(): void
    {
        $Response = $this->call('/demo/user/create', ['name' => '   ', 'email' => 'ann@example.com']);

        self::assertStatus(400, $Response);
        self::assertSame('Param [name] must be a non empty string', $Response->getMessage());
    }

    public function testCreateMissingEmailIs400Dependency(): void
    {
        $Response = $this->call('/demo/user/create', ['name' => 'Ann']);

        self::assertStatus(400, $Response);
        self::assertSame(ResponseType::Dependency, $Response->getResponseType());
        self::assertSame('Missing param [email] in request body->params', $Response->getMessage());
        self::assertFileDoesNotExist(self::demoVar() . '/data/users.json');
    }

    public function testCreateBadEmailIs422(): void
    {
        $Response = $this->call('/demo/user/create', ['name' => 'Ann', 'email' => 'not-an-email']);

        self::assertStatus(422, $Response);
        self::assertSame(ResponseType::Dependency, $Response->getResponseType());
        self::assertSame('Email [not-an-email] is not valid', $Response->getMessage());

        $Response = $this->call('/demo/user/create', ['name' => 'Ann', 'email' => 42]);
        self::assertStatus(422, $Response);
        self::assertSame('Email [int] is not valid', $Response->getMessage());
    }

    public function testReadListAndOne(): void
    {
        $list = $this->call('/demo/user/read');
        self::assertStatus(200, $list);
        self::assertSame(['users' => []], $list->getPayload());

        $ann = $this->createUser('Ann', 'ann@example.com');
        $bob = $this->createUser('Bob', 'bob@example.com');

        $list = $this->call('/demo/user/read');
        self::assertStatus(200, $list);
        self::assertSame(['users' => [$ann, $bob]], $list->getPayload());

        $one = $this->call('/demo/user/read/id/2');
        self::assertStatus(200, $one);
        self::assertSame(ResponseType::Response, $one->getResponseType());
        self::assertSame($bob, $one->getPayload());

        $_GET = ['id' => '1'];
        self::assertSame($ann, $this->call('/demo/user/read')->getPayload());
    }

    public function testReadOneFromFormPost(): void
    {
        $ann = $this->createUser('Ann', 'ann@example.com');
        $_POST = ['id' => '1'];

        $one = $this->call('/demo/user/read');

        self::assertStatus(200, $one);
        self::assertSame($ann, $one->getPayload());
    }

    public function testReadUnknownIs404(): void
    {
        $Response = $this->call('/demo/user/read/id/9');

        self::assertStatus(404, $Response);
        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertSame('User [9] not found', $Response->getMessage());
    }

    public function testReadNonNumericIdIs400(): void
    {
        $Response = $this->call('/demo/user/read/id/abc');

        self::assertStatus(400, $Response);
        self::assertSame(ResponseType::Dependency, $Response->getResponseType());
        self::assertSame('GET param [id] is not an unsigned int, it is: [abc]', $Response->getMessage());
    }

    public function testUpdateChangesFields(): void
    {
        $this->createUser('Ann', 'ann@example.com');

        $Response = $this->call('/demo/user/update/id/1', ['name' => ' Anna ']);
        self::assertStatus(204, $Response);
        self::assertSame(['id' => 1, 'name' => 'Anna', 'email' => 'ann@example.com'], $Response->getPayload());

        $Response = $this->call('/demo/user/update/id/1', ['email' => 'anna@example.com']);
        self::assertSame(['id' => 1, 'name' => 'Anna', 'email' => 'anna@example.com'], $Response->getPayload());

        self::assertSame(['id' => 1, 'name' => 'Anna', 'email' => 'anna@example.com'], $this->call('/demo/user/read/id/1')->getPayload());
    }

    public function testUpdateErrors(): void
    {
        $this->createUser('Ann', 'ann@example.com');

        $Response = $this->call('/demo/user/update/id/7', ['name' => 'Nobody']);
        self::assertStatus(404, $Response);
        self::assertSame('User [7] not found', $Response->getMessage());

        $Response = $this->call('/demo/user/update/id/1');
        self::assertStatus(400, $Response);
        self::assertSame(ResponseType::Dependency, $Response->getResponseType());

        $Response = $this->call('/demo/user/update/id/1', ['email' => 'nope']);
        self::assertStatus(400, $Response);
        self::assertSame('Param [email] is not a valid email', $Response->getMessage());

        $Response = $this->call('/demo/user/update/id/1', ['name' => ['Ann']]);
        self::assertStatus(400, $Response);
        self::assertSame('Param [name] must be a non empty string', $Response->getMessage());

        self::assertSame(['id' => 1, 'name' => 'Ann', 'email' => 'ann@example.com'], $this->call('/demo/user/read/id/1')->getPayload());
    }

    public function testDeleteRemoves(): void
    {
        $this->createUser('Ann', 'ann@example.com');

        $Response = $this->call('/demo/user/delete/id/1');
        self::assertStatus(204, $Response);
        self::assertSame(['deleted' => 1], $Response->getPayload());

        self::assertStatus(404, $this->call('/demo/user/read/id/1'));

        $Response = $this->call('/demo/user/delete/id/1');
        self::assertStatus(404, $Response);
        self::assertSame('User [1] not found', $Response->getMessage());
    }

    public function testOptionsListsMethods(): void
    {
        $Response = $this->call('/demo/user/options');

        self::assertStatus(204, $Response);
        self::assertSame(['Allow: OPTIONS,GET,POST,PUT,DELETE'], $Response->getHeaders());
    }

    public function testDemoLoggerWritesOneLinePerCall(): void
    {
        $log = self::demoVar() . '/log/demo.log';
        if (is_file($log)) {
            unlink($log);
        }

        $logger = TalisMain::logger();
        $logger->debug('plain text');
        $logger->info(['a' => 1], false);
        $logger->warning("two\nlines", true);
        $logger->error(new \RuntimeException('boom'), true);
        $logger->fatal(new Response(), false);

        $lines = file($log, FILE_IGNORE_NEW_LINES);
        self::assertIsArray($lines);
        self::assertCount(5, $lines);
        self::assertMatchesRegularExpression('/^\[[^\]]+\] DEBUG plain text$/', $lines[0]);
        self::assertStringEndsWith('INFO {"a":1}', $lines[1]);
        self::assertStringEndsWith('WARNING two\nlines', $lines[2]);
        self::assertStringContainsString('ERROR RuntimeException: boom at ', $lines[3]);
        self::assertStringContainsString('FATAL {"status":"500 Server Error"', $lines[4]);
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Integration\Support;

/**
 * Runs the demo standalone router under `php -S 127.0.0.1:<port>`, with
 * TALIS_DEMO_VAR pointed at a fresh temp folder. start() waits until the
 * port answers (or throws after 5s); stop() (call it from tearDown())
 * terminates the process.
 */
final class DemoServer
{
    private const float START_TIMEOUT_SECONDS = 5.0;

    private readonly string $repoRoot;

    private readonly string $varDir;

    private int $port = 0;

    /**
     * @var resource|null
     */
    private $process = null;

    public function __construct()
    {
        $this->repoRoot = dirname(__DIR__, 3);
        $this->varDir = sys_get_temp_dir() . '/talis-integration-server-' . bin2hex(random_bytes(6));
    }

    public function start(): void
    {
        $this->port = $this->findFreePort();
        $router = $this->repoRoot . '/demo/doors/standalone/router.php';

        putenv('TALIS_DEMO_VAR=' . $this->varDir);
        $process = proc_open(
            [PHP_BINARY, '-S', "127.0.0.1:{$this->port}", $router],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            $this->repoRoot,
        );
        putenv('TALIS_DEMO_VAR');

        if ($process === false) {
            throw new \RuntimeException('Could not start the demo standalone server');
        }
        $this->process = $process;

        $this->waitUntilUp();
    }

    public function stop(): void
    {
        if ($this->process === null) {
            return;
        }
        proc_terminate($this->process);
        proc_close($this->process);
        $this->process = null;
    }

    public function baseUrl(): string
    {
        return "http://127.0.0.1:{$this->port}";
    }

    public function varDir(): string
    {
        return $this->varDir;
    }

    private function findFreePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            throw new \RuntimeException("Could not find a free port: {$errstr}");
        }
        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);

        $port = (int) substr($name, (int) strrpos($name, ':') + 1);
        if ($port <= 0) {
            throw new \RuntimeException("Could not parse a port out of [{$name}]");
        }
        return $port;
    }

    private function waitUntilUp(): void
    {
        $deadline = microtime(true) + self::START_TIMEOUT_SECONDS;
        while (microtime(true) < $deadline) {
            $connection = @fsockopen('127.0.0.1', $this->port, $errno, $errstr, 0.2);
            if (is_resource($connection)) {
                fclose($connection);
                return;
            }
            usleep(50_000);
        }

        $this->stop();
        throw new \RuntimeException("Demo server did not start listening on 127.0.0.1:{$this->port} within " . self::START_TIMEOUT_SECONDS . 's');
    }
}

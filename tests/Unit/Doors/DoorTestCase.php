<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use PHPUnit\Framework\TestCase;
use Talis\Message\Response;
use Talis\Router\DefaultRouter;
use Talis\TalisMain;
use Talis\Tests\Support\SpyEmitter;
use Talis\Tests\Support\SpyLogger;

/**
 * Shared setup for the door tests: the fixture app, a spy logger and a
 * spy emitter. Saves and restores the superglobals the doors read.
 */
abstract class DoorTestCase extends TestCase
{
    protected SpyLogger $logger;

    protected SpyEmitter $emitter;

    /**
     * @var array<array-key, mixed>
     */
    private array $savedServer = [];

    /**
     * @var array<array-key, mixed>
     */
    private array $savedGet = [];

    /**
     * @var array<array-key, mixed>
     */
    private array $savedPost = [];

    protected function setUp(): void
    {
        $this->savedServer = $_SERVER;
        $this->savedGet = $_GET;
        $this->savedPost = $_POST;
        $_GET = [];
        $_POST = [];

        TalisMain::$appPath = __DIR__ . '/../../Fixtures/app';
        TalisMain::$registeredRouter = DefaultRouter::class;
        TalisMain::$registeredInitFunc = null;
        $this->logger = new SpyLogger();
        TalisMain::setLogger($this->logger);
        $this->emitter = new SpyEmitter();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->savedServer;
        $_GET = $this->savedGet;
        $_POST = $this->savedPost;
        TalisMain::$appPath = '';
        TalisMain::setLogger(new \Talis\commons\NullLogger());
    }

    /**
     * Every logged value for one level, in order.
     *
     * @return list<mixed>
     */
    protected function logged(string $level): array
    {
        $found = [];
        foreach ($this->logger->calls as $call) {
            if ($call['level'] === $level) {
                $found[] = $call['inp'];
            }
        }
        return $found;
    }

    protected function assertLoggedWith(string $level, mixed $inp, bool $fullStack): void
    {
        self::assertContains(
            ['level' => $level, 'inp' => $inp, 'full_stack' => $fullStack],
            $this->logger->calls,
            "No {$level} log call found with the given value",
        );
    }

    protected function emittedOnce(): Response
    {
        self::assertCount(1, $this->emitter->emitted);
        return $this->emitter->last();
    }

    protected static function code(Response $Response): int
    {
        return $Response->getStatus()->getCode();
    }

    protected static function payloadBody(Response $Response): mixed
    {
        $payload = $Response->getPayload();
        self::assertInstanceOf(\stdClass::class, $payload);
        return $payload->body ?? null;
    }
}

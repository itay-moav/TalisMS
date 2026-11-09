<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Router;

use PHPUnit\Framework\TestCase;
use Talis\commons\NullLogger;
use Talis\TalisMain;
use Talis\Tests\Fixtures\Router\ExposingRouter;
use Talis\Tests\Support\SpyLogger;

final class aRouterTest extends TestCase
{
    /**
     * @var array<array-key, mixed>
     */
    private array $savedGet = [];

    protected function setUp(): void
    {
        $this->savedGet = $_GET;
    }

    protected function tearDown(): void
    {
        $_GET = $this->savedGet;
        TalisMain::setLogger(new NullLogger());
    }

    public function testConstructorStoresPartsAndGetParams(): void
    {
        $router = new ExposingRouter(['', 'a'], ['q' => '1']);

        self::assertSame(['', 'a'], $router->parts());
        self::assertSame(['q' => '1'], $router->getParams());
    }

    public function testGetParamsDefaultToSuperGlobal(): void
    {
        $_GET = ['x' => 'y'];

        $router = new ExposingRouter(['', 'a']);

        self::assertSame(['x' => 'y'], $router->getParams());
    }

    public function testConstructorLogsParts(): void
    {
        $spy = new SpyLogger();
        TalisMain::setLogger($spy);

        new ExposingRouter(['', 'a', 'b']);

        self::assertContains(
            ['level' => 'debug', 'inp' => ['', 'a', 'b'], 'full_stack' => null],
            $spy->calls,
        );
    }
}

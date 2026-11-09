<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\ChainContainer;
use Talis\TalisMain;
use Talis\Tests\Fixtures\Chain\LinkA;
use Talis\Tests\Fixtures\Chain\LinkB;
use Talis\Tests\Support\SpyLogger;

final class ChainContainerTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        TalisMain::setLogger(new \Talis\commons\NullLogger());
    }

    public function testNewContainerIsEmpty(): void
    {
        self::assertTrue((new ChainContainer())->isEmpty());
    }

    public function testConstructorEntriesAreNotEmpty(): void
    {
        self::assertFalse((new ChainContainer([[LinkA::class, []]]))->isEmpty());
    }

    public function testPopReturnsFirstInFirstOut(): void
    {
        $container = new ChainContainer();
        $container->push([LinkA::class, ['a']]);
        $container->push([LinkB::class, ['b']]);

        self::assertSame([LinkA::class, ['a']], $container->pop());
        self::assertSame([LinkB::class, ['b']], $container->pop());
        self::assertTrue($container->isEmpty());
    }

    public function testPopOnEmptyThrows(): void
    {
        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('Chain container is empty');
        (new ChainContainer())->pop();
    }

    public function testPushReturnsSelf(): void
    {
        $container = new ChainContainer();

        self::assertSame($container, $container->push([LinkA::class, []]));
    }

    public function testClearEmptiesContainer(): void
    {
        $container = new ChainContainer([[LinkA::class, []], [LinkB::class, []]]);

        $container->clear();

        self::assertTrue($container->isEmpty());
    }

    public function testDebugLogsEveryEntry(): void
    {
        $logger = new SpyLogger();
        TalisMain::setLogger($logger);
        $container = new ChainContainer([[LinkA::class, []], [LinkB::class, ['x']]]);

        $container->debug();

        self::assertSame([
            ['level' => 'debug', 'inp' => 'Chain link 0', 'full_stack' => null],
            ['level' => 'debug', 'inp' => [LinkA::class, []], 'full_stack' => null],
            ['level' => 'debug', 'inp' => 'Chain link 1', 'full_stack' => null],
            ['level' => 'debug', 'inp' => [LinkB::class, ['x']], 'full_stack' => null],
        ], $logger->calls);
    }
}

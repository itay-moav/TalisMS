<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Dependencies;

use PHPUnit\Framework\TestCase;
use Talis\Chain\ChainContainer;
use Talis\Chain\Errors\BLLinkMissingInChain;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\FailingDependency;
use Talis\Tests\Fixtures\Chain\LinkA;
use Talis\Tests\Fixtures\Chain\PassingDependency;
use Talis\Tests\Fixtures\Chain\Recorder;

final class aDependencyTest extends TestCase
{
    protected function setUp(): void
    {
        Recorder::reset();
    }

    public function testEmptyContainerReturnsBlLinkMissing(): void
    {
        $dependency = new PassingDependency(new Request('/x'), new Response());

        $result = $dependency->process();

        self::assertInstanceOf(BLLinkMissingInChain::class, $result);
    }

    public function testValidKeepsChain(): void
    {
        $dependency = new PassingDependency(new Request('/x'), new Response());
        $container = new ChainContainer([[LinkA::class, []]]);
        $dependency->setChainContainer($container);

        $result = $dependency->process();

        self::assertSame($dependency, $result);
        self::assertFalse($container->isEmpty());
    }

    public function testInvalidClearsChain(): void
    {
        $dependency = new FailingDependency(new Request('/x'), new Response());
        $container = new ChainContainer([[LinkA::class, []]]);
        $dependency->setChainContainer($container);

        $result = $dependency->process();

        self::assertSame($dependency, $result);
        self::assertTrue($container->isEmpty());
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\DoneSuccessful;
use Talis\Chain\Errors\BLLinkMissingInChain;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\CallableBlHead;
use Talis\Tests\Fixtures\Chain\EmptyHead;
use Talis\Tests\Fixtures\Chain\FailingDependency;
use Talis\Tests\Fixtures\Chain\FailingDependencyHead;
use Talis\Tests\Fixtures\Chain\NoBlHead;
use Talis\Tests\Fixtures\Chain\OrderedHead;
use Talis\Tests\Fixtures\Chain\Recorder;

final class aFilteredValidatedChainLinkTest extends TestCase
{
    protected function setUp(): void
    {
        Recorder::reset();
    }

    public function testChainOrderIsFiltersDependenciesThenBl(): void
    {
        (new OrderedHead(new Request('/x'), new Response()))->nextLinkInChain();

        self::assertSame(['filter', 'dependency', 'A'], Recorder::$ran);
    }

    public function testProcessReturnsSelf(): void
    {
        $Response = new Response();
        $head = new OrderedHead(new Request('/x'), $Response);

        self::assertSame($head, $head->process());
        self::assertEquals(new Response(), $Response);
        self::assertSame([], Recorder::$ran);
    }

    public function testFullChainEndsInBlLink(): void
    {
        $result = (new OrderedHead(new Request('/x'), new Response()))->nextLinkInChain();

        self::assertInstanceOf(DoneSuccessful::class, $result);
        self::assertSame(200, $result->getResponse()->getStatus()->getCode());
    }

    public function testFailedDependencyStopsChain(): void
    {
        $result = (new FailingDependencyHead(new Request('/x'), new Response()))->nextLinkInChain();

        self::assertInstanceOf(FailingDependency::class, $result);
        self::assertSame(['dependency'], Recorder::$ran);
    }

    public function testDependencyWithNoBlReturnsBlLinkMissing(): void
    {
        $result = (new NoBlHead(new Request('/x'), new Response()))->nextLinkInChain();

        self::assertInstanceOf(BLLinkMissingInChain::class, $result);
    }

    public function testCallableInGetNextBlRuns(): void
    {
        $Response = new Response();

        (new CallableBlHead(new Request('/x'), $Response))->nextLinkInChain();

        self::assertEquals((object) ['fromClosure' => 'yes'], $Response->getPayload());
    }

    public function testEmptyEverythingReturnsHead(): void
    {
        $head = new EmptyHead(new Request('/x'), new Response());

        self::assertSame($head, $head->nextLinkInChain());
    }
}

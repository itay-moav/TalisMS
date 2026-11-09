<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Filters;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Filters\AddGetQueryStringOverriding;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\ProbeLink;
use Talis\Tests\Fixtures\Chain\Recorder;

final class AddGetQueryStringOverridingTest extends TestCase
{
    protected function setUp(): void
    {
        Recorder::reset();
        ProbeLink::$last = null;
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testGetOverridesRouteParams(): void
    {
        $_GET = ['id' => '2', 'x' => 'y'];
        $filter = new AddGetQueryStringOverriding(new Request('/x', ['id' => '1']), new Response());
        $filter->appendChainLink(ProbeLink::class);

        $filter->nextLinkInChain();

        self::assertSame(['id' => '2', 'x' => 'y'], ProbeLink::$last?->request()->getAllGetParams());
    }

    public function testUriAndBodyKept(): void
    {
        $_GET = [];
        $body = (object) ['a' => 1];
        $filter = new AddGetQueryStringOverriding(new Request('/x', [], $body), new Response());
        $filter->appendChainLink(ProbeLink::class);

        $filter->nextLinkInChain();

        $last = ProbeLink::$last;
        self::assertNotNull($last);
        self::assertSame('/x', $last->request()->getUri());
        self::assertSame($body, $last->request()->getBody());
    }

    public function testArrayValueKept(): void
    {
        $_GET = ['a' => ['1']];
        $filter = new AddGetQueryStringOverriding(new Request('/x'), new Response());
        $filter->appendChainLink(ProbeLink::class);

        $filter->nextLinkInChain();

        self::assertSame(['1'], ProbeLink::$last?->request()->getAllGetParams()['a']);
    }

    public function testNumericKeysKept(): void
    {
        $_GET = ['0' => 'zero'];
        $filter = new AddGetQueryStringOverriding(new Request('/x', ['0' => 'original']), new Response());
        $filter->appendChainLink(ProbeLink::class);

        $filter->nextLinkInChain();

        self::assertSame('zero', ProbeLink::$last?->request()->getAllGetParams()[0]);
    }
}

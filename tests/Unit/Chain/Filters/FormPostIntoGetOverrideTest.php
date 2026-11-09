<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Filters;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Filters\FormPostIntoGetOverride;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\ProbeLink;
use Talis\Tests\Fixtures\Chain\Recorder;

final class FormPostIntoGetOverrideTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        Recorder::reset();
        ProbeLink::$last = null;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $_POST = [];
    }

    public function testPostOverridesGetParams(): void
    {
        $_POST = ['a' => '2'];
        $filter = new FormPostIntoGetOverride(new Request('/x', ['a' => '1']), new Response());
        $filter->appendChainLink(ProbeLink::class);

        $filter->nextLinkInChain();

        self::assertSame('2', ProbeLink::$last?->request()->getAllGetParams()['a']);
    }

    public function testArrayValueKept(): void
    {
        $_POST = ['x' => ['1']];
        $filter = new FormPostIntoGetOverride(new Request('/x'), new Response());
        $filter->appendChainLink(ProbeLink::class);

        $filter->nextLinkInChain();

        self::assertSame(['1'], ProbeLink::$last?->request()->getAllGetParams()['x']);
    }

    public function testNumericKeysKept(): void
    {
        $_POST = ['0' => 'zero'];
        $filter = new FormPostIntoGetOverride(new Request('/x', ['0' => 'original']), new Response());
        $filter->appendChainLink(ProbeLink::class);

        $filter->nextLinkInChain();

        self::assertSame('zero', ProbeLink::$last?->request()->getAllGetParams()[0]);
    }
}

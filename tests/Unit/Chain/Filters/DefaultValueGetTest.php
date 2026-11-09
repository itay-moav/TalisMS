<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Filters;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Filters\DefaultValueGet;
use Talis\Exception\InvalidChainEntry;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\ProbeLink;
use Talis\Tests\Fixtures\Chain\Recorder;

final class DefaultValueGetTest extends TestCase
{
    protected function setUp(): void
    {
        Recorder::reset();
        ProbeLink::$last = null;
    }

    private function filtered(Request $Request, string $field, string $default): string
    {
        $filter = new DefaultValueGet($Request, new Response(), ['field' => $field, 'default' => $default]);
        $filter->appendChainLink(ProbeLink::class);
        $filter->nextLinkInChain();

        $params = ProbeLink::$last?->request()->getAllGetParams() ?? [];
        /** @var string $value */
        $value = $params[$field];
        return $value;
    }

    public function testMissingGetsDefault(): void
    {
        self::assertSame('1', $this->filtered(new Request('/x'), 'page', '1'));
    }

    public function testEmptyStringGetsDefault(): void
    {
        self::assertSame('1', $this->filtered(new Request('/x', ['page' => '']), 'page', '1'));
    }

    public function testZeroIsKept(): void
    {
        self::assertSame('0', $this->filtered(new Request('/x', ['page' => '0']), 'page', '1'));
    }

    public function testExistingValueKept(): void
    {
        self::assertSame('3', $this->filtered(new Request('/x', ['page' => '3']), 'page', '1'));
    }

    public function testMissingDefaultParamThrows(): void
    {
        $filter = new DefaultValueGet(new Request('/x'), new Response(), ['field' => 'page']);

        $this->expectException(InvalidChainEntry::class);
        $filter->process();
    }
}

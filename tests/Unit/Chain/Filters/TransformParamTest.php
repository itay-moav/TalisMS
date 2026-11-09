<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Filters;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Filters\TransformParam;
use Talis\Exception\InvalidChainEntry;
use Talis\Message\Request;
use Talis\Message\Response;

final class TransformParamTest extends TestCase
{
    /**
     * A plain string param (not a literal) keeps phpstan from treating the
     * mutated property as a fixed literal value. The returned object is
     * params itself, so reading it back is a single property access, not a
     * chain through an untyped body->params.
     */
    private function statusParams(string $value): \stdClass
    {
        return (object) ['status' => $value];
    }

    private function nParams(int $value): \stdClass
    {
        return (object) ['n' => $value];
    }

    public function testMatchingValueIsReplaced(): void
    {
        $params = $this->statusParams('old');
        $body = (object) ['params' => $params];
        $filter = new TransformParam(new Request('/x', [], $body), new Response(), ['status', 'old', 'new']);

        $filter->process();

        self::assertSame('new', $params->status);
    }

    public function testLooseMatchReplaces(): void
    {
        $params = $this->nParams(1);
        $body = (object) ['params' => $params];
        $filter = new TransformParam(new Request('/x', [], $body), new Response(), ['n', '1', 'one']);

        $filter->process();

        self::assertSame('one', $params->n);
    }

    public function testNonMatchingUntouched(): void
    {
        $params = $this->statusParams('x');
        $body = (object) ['params' => $params];
        $filter = new TransformParam(new Request('/x', [], $body), new Response(), ['status', 'old', 'new']);

        $filter->process();

        self::assertSame('x', $params->status);
    }

    public function testMissingFieldUntouched(): void
    {
        $body = (object) ['params' => (object) []];
        $filter = new TransformParam(new Request('/x', [], $body), new Response(), ['status', 'old', 'new']);

        $filter->process();

        self::assertFalse(isset($body->params->status));
    }

    public function testNoParamsIsNoop(): void
    {
        $body = (object) [];
        $filter = new TransformParam(new Request('/x', [], $body), new Response(), ['status', 'old', 'new']);

        $filter->process();

        self::assertEquals((object) [], $body);
    }

    public function testMissingToValueThrows(): void
    {
        $filter = new TransformParam(new Request('/x'), new Response(), ['status', 'old']);

        $this->expectException(InvalidChainEntry::class);
        $filter->process();
    }
}

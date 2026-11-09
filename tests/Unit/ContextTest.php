<?php

declare(strict_types=1);

namespace Talis\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Talis\Context;

final class ContextTest extends TestCase
{
    public function testHasIsFalseForUnknownName(): void
    {
        $context = new Context();

        self::assertFalse($context->has('nope'));
    }

    public function testGetReturnsNullForUnknownName(): void
    {
        $context = new Context();

        self::assertNull($context->get('nope'));
    }

    public function testGetMissingReturnsDefault(): void
    {
        $context = new Context();

        self::assertSame('dflt', $context->get('nope', 'dflt'));
    }

    public function testSetThenGet(): void
    {
        $context = new Context();
        $value = new \stdClass();

        $context->set('db', $value);

        self::assertSame($value, $context->get('db'));
        self::assertTrue($context->has('db'));
    }

    public function testSetOverwrites(): void
    {
        $context = new Context();

        $context->set('k', 1);
        $context->set('k', 2);

        self::assertSame(2, $context->get('k'));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function falsyValues(): iterable
    {
        yield 'zero' => [0];
        yield 'empty string' => [''];
        yield 'false' => [false];
        yield 'null' => [null];
        yield 'numeric string zero' => ['0'];
        yield 'empty array' => [[]];
    }

    #[DataProvider('falsyValues')]
    public function testFalsyValuesAreStored(mixed $value): void
    {
        $context = new Context();

        $context->set('a', $value);

        self::assertTrue($context->has('a'));
        self::assertSame($value, $context->get('a'));
        self::assertSame($value, $context->get('a', 'dflt'));
    }
}

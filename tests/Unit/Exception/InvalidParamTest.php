<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Talis\Exception\InvalidParam;

final class InvalidParamTest extends TestCase
{
    public function testMessageAndDefaults(): void
    {
        $e = new InvalidParam('id', 'must be a single value');

        self::assertSame('Param [id] must be a single value', $e->getMessage());
        self::assertSame(0, $e->getCode());
        self::assertNull($e->getPrevious());
    }

    public function testPreviousIsKept(): void
    {
        $previous = new \RuntimeException('cause');

        $e = new InvalidParam('id', 'must be a single value', $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Talis\Exception\InvalidChainEntry;

final class InvalidChainEntryTest extends TestCase
{
    public function testMessageAndDefaults(): void
    {
        $e = new InvalidChainEntry('x');

        self::assertSame('Bad chain link entry: x', $e->getMessage());
        self::assertSame(0, $e->getCode());
        self::assertNull($e->getPrevious());
    }

    public function testPreviousIsKept(): void
    {
        $previous = new \RuntimeException('cause');

        $e = new InvalidChainEntry('x', $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}

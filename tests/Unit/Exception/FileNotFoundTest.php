<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Talis\Exception\FileNotFound;

final class FileNotFoundTest extends TestCase
{
    public function testMessageAndDefaults(): void
    {
        $e = new FileNotFound('/x');

        self::assertSame('File [/x] not found', $e->getMessage());
        self::assertSame(0, $e->getCode());
        self::assertNull($e->getPrevious());
    }

    public function testPreviousIsKept(): void
    {
        $previous = new \RuntimeException('cause');

        $e = new FileNotFound('/x', $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}

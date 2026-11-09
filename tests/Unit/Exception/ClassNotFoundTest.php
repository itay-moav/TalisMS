<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Talis\Exception\ClassNotFound;

final class ClassNotFoundTest extends TestCase
{
    public function testMessageAndDefaults(): void
    {
        $e = new ClassNotFound('Foo');

        self::assertSame('Class [Foo] not found', $e->getMessage());
        self::assertSame(0, $e->getCode());
        self::assertNull($e->getPrevious());
    }

    public function testPreviousIsKept(): void
    {
        $previous = new \RuntimeException('cause');

        $e = new ClassNotFound('Foo', $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Talis\Exception\ParamNotFound;

final class ParamNotFoundTest extends TestCase
{
    public function testMessageAndDefaults(): void
    {
        $e = new ParamNotFound('id');

        self::assertSame('Param [id] not found', $e->getMessage());
        self::assertSame(0, $e->getCode());
        self::assertNull($e->getPrevious());
    }

    public function testPreviousIsKept(): void
    {
        $previous = new \RuntimeException('cause');

        $e = new ParamNotFound('id', $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}

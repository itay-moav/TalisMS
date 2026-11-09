<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Talis\Exception\BadUri;

final class BadUriTest extends TestCase
{
    public function testMessageAndDefaults(): void
    {
        $e = new BadUri('/a/b');

        self::assertSame('Bad URI [/a/b]', $e->getMessage());
        self::assertSame(0, $e->getCode());
        self::assertNull($e->getPrevious());
    }

    public function testPreviousIsKept(): void
    {
        $previous = new \RuntimeException('cause');

        $e = new BadUri('/a/b', $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}

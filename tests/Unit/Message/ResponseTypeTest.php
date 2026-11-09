<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use Talis\Message\ResponseType;

final class ResponseTypeTest extends TestCase
{
    public function testCaseValues(): void
    {
        self::assertEquals('response', ResponseType::Response->value);
        self::assertEquals('dependency', ResponseType::Dependency->value);
        self::assertEquals('error', ResponseType::Error->value);
    }

    public function testCasesCount(): void
    {
        self::assertEquals(3, count(ResponseType::cases()));
    }

    public function testFromString(): void
    {
        self::assertEquals(ResponseType::Error, ResponseType::from('error'));
        self::assertEquals(null, ResponseType::tryFrom('nope'));
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Talis\commons\NullLogger;
use Talis\TalisMain;
use Talis\Tests\Support\SpyLogger;

final class TalisMainLoggerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->resetLogger();
    }

    protected function tearDown(): void
    {
        $this->resetLogger();
    }

    private function resetLogger(): void
    {
        $property = new \ReflectionProperty(TalisMain::class, 'logger');
        $property->setValue(null, null);
    }

    public function testLoggerFallsBackToNullLogger(): void
    {
        self::assertInstanceOf(NullLogger::class, TalisMain::logger());
    }

    public function testSetLoggerIsUsed(): void
    {
        $spy = new SpyLogger();

        TalisMain::setLogger($spy);

        self::assertSame($spy, TalisMain::logger());
    }
}

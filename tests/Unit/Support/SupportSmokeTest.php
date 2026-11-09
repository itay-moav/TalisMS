<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;
use Talis\Tests\Support\SpyLogger;

final class SupportSmokeTest extends TestCase
{
    public function testSpyLoggerRecordsLevelAndInput(): void
    {
        $logger = new SpyLogger();

        $logger->debug('x');

        self::assertSame(
            ['level' => 'debug', 'inp' => 'x', 'full_stack' => null],
            $logger->calls[0],
        );
    }

    public function testSpyEmitterKeepsLastResponse(): void
    {
        $emitter = new SpyEmitter();
        $response = new Response();

        $emitter->emit($response);

        self::assertSame($response, $emitter->last());
    }
}

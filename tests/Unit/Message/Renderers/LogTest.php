<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Message\Renderers;

use PHPUnit\Framework\TestCase;
use Talis\Message\Renderers\Log;
use Talis\Message\Response;
use Talis\Message\Status\Code200;
use Talis\TalisMain;
use Talis\Tests\Support\SpyLogger;

final class LogTest extends TestCase
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

    public function testEmitResponseTypeLogsDebug(): void
    {
        $response = new Response();
        $response->setStatus(new Code200());
        $response->markResponse();
        $spy = new SpyLogger();
        TalisMain::setLogger($spy);
        $log = new Log();

        $log->emit($response);

        self::assertSame([
            ['level' => 'debug', 'inp' => 'END OF PROCESS', 'full_stack' => null],
            ['level' => 'debug', 'inp' => json_encode($response->getBody()), 'full_stack' => null],
        ], $spy->calls);
    }

    public function testEmitErrorTypeLogsFatalWithFullStack(): void
    {
        $response = new Response();
        $response->setStatus(new Code200());
        $response->markError();
        $spy = new SpyLogger();
        TalisMain::setLogger($spy);
        $log = new Log();

        $log->emit($response);

        self::assertSame([
            ['level' => 'fatal', 'inp' => 'CHAIN BROKE ' . json_encode($response->getBody()), 'full_stack' => true],
        ], $spy->calls);
    }

    public function testEmitDependencyTypeLogsFatal(): void
    {
        $response = new Response();
        $response->setStatus(new Code200());
        $response->markDependency();
        $spy = new SpyLogger();
        TalisMain::setLogger($spy);
        $log = new Log();

        $log->emit($response);

        self::assertSame([
            ['level' => 'fatal', 'inp' => 'CHAIN BROKE ' . json_encode($response->getBody()), 'full_stack' => true],
        ], $spy->calls);
    }

    public function testEmitPrintsNothing(): void
    {
        $response = new Response();
        $response->setStatus(new Code200());
        $log = new Log();

        $this->expectOutputString('');
        $log->emit($response);
    }

    public function testEmitThrowsJsonExceptionOnUnencodablePayload(): void
    {
        $response = new Response();
        $response->setPayload(INF);
        $spy = new SpyLogger();
        TalisMain::setLogger($spy);
        $log = new Log();

        try {
            $log->emit($response);
            self::fail('Expected a JsonException to be thrown.');
        } catch (\JsonException) {
        }

        self::assertSame([], $spy->calls);
    }
}

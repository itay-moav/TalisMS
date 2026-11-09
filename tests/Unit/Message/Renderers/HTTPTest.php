<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Message\Renderers;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Talis\Message\Renderers\HTTP;
use Talis\Message\Response;
use Talis\Message\Status\Code201;
use Talis\Message\Status\Code404;
use Talis\TalisMain;
use Talis\Tests\Support\SpyLogger;

final class HTTPTest extends TestCase
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

    /**
     * @return list<string>
     */
    private function captureHeaders(Response $response): array
    {
        $captured = [];
        $http = new HTTP(function (string $header) use (&$captured): void {
            $captured[] = $header;
        });

        ob_start();
        $http->emit($response);
        ob_end_clean();

        return $captured;
    }

    public function testEmitSendsStatusLineContentTypeAndExtraHeadersInOrder(): void
    {
        $response = new Response();
        $response->setStatus(new Code404());
        $response->setHeader('X-A: 1');
        $response->setHeader('X-B: 2');

        $captured = $this->captureHeaders($response);

        self::assertSame([
            'HTTP/1.1 404 Not Found',
            'Content-Type: application/json; charset=utf-8',
            'X-A: 1',
            'X-B: 2',
        ], $captured);
    }

    public function testEmitWithNoExtraHeadersSendsTwoHeaders(): void
    {
        $response = new Response();

        $captured = $this->captureHeaders($response);

        self::assertCount(2, $captured);
    }

    public function testEmitEchoesJsonBody(): void
    {
        $response = new Response();
        $response->setHeader('X-A: 1');
        $http = new HTTP(function (string $header): void {
        });

        ob_start();
        $http->emit($response);
        $output = ob_get_clean();

        self::assertSame(json_encode($response->getBody()), $output);
    }

    public function testEmitLogsEveryHeaderIncludingFirstCustomOne(): void
    {
        $response = new Response();
        $response->setStatus(new Code404());
        $response->setHeader('X-A: 1');
        $response->setHeader('X-B: 2');
        $spy = new SpyLogger();
        TalisMain::setLogger($spy);
        $http = new HTTP(function (string $header): void {
        });

        ob_start();
        $http->emit($response);
        ob_end_clean();

        self::assertSame('debug', $spy->calls[0]['level']);
        self::assertSame('SENDING HEADERS', $spy->calls[0]['inp']);

        self::assertSame('debug', $spy->calls[1]['level']);
        self::assertSame([
            'HTTP/1.1 404 Not Found',
            'Content-Type: application/json; charset=utf-8',
            'X-A: 1',
            'X-B: 2',
        ], $spy->calls[1]['inp']);

        self::assertSame('debug', $spy->calls[2]['level']);
        self::assertSame('SENDING BODY', $spy->calls[2]['inp']);

        self::assertSame('debug', $spy->calls[3]['level']);
        self::assertSame(json_encode($response->getBody()), $spy->calls[3]['inp']);

        self::assertCount(4, $spy->calls);
    }

    public function testEmitWithoutStatusSends500(): void
    {
        $response = new Response();

        $captured = $this->captureHeaders($response);

        self::assertSame('HTTP/1.1 500 Server Error', $captured[0]);
    }

    public function testEmitThrowsJsonExceptionBeforeSendingAnything(): void
    {
        $response = new Response();
        $response->setPayload(INF);
        $captured = [];
        $http = new HTTP(function (string $header) use (&$captured): void {
            $captured[] = $header;
        });

        ob_start();
        try {
            $http->emit($response);
            self::fail('Expected a JsonException to be thrown.');
        } catch (\JsonException) {
        }
        $output = ob_get_clean();

        self::assertSame([], $captured);
        self::assertSame('', $output);
    }

    #[RunInSeparateProcess]
    public function testDefaultConstructorSendsRealHeaders(): void
    {
        $response = new Response();
        $response->setStatus(new Code201());
        $http = new HTTP();

        ob_start();
        $http->emit($response);
        ob_end_clean();

        self::assertSame(201, http_response_code());
    }
}

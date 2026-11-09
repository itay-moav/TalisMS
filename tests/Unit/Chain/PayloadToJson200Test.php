<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Talis\Chain\PayloadToJson200;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Tests\Fixtures\Chain\CapturingJson200;
use Talis\Tests\Support\SpyEmitter;

final class PayloadToJson200Test extends TestCase
{
    public function testProcessSets200(): void
    {
        $Response = new Response();
        $Response->markError();
        $link = new PayloadToJson200(new Request('/x'), $Response);

        self::assertSame($link, $link->process());
        self::assertSame(200, $Response->getStatus()->getCode());
        self::assertSame(ResponseType::Response, $Response->getResponseType());
    }

    public function testRenderEchoesJsonPayload(): void
    {
        $Response = new Response();
        $Response->setPayload(['a' => 1]);
        $link = new CapturingJson200(new Request('/x'), $Response);

        $this->expectOutputString('{"a":1}');
        $link->render(new SpyEmitter());
    }

    public function testRenderSendsStatusContentTypeAndResponseHeaders(): void
    {
        $Response = new Response();
        $Response->setHeader('X-A: 1');
        $link = new CapturingJson200(new Request('/x'), $Response);

        ob_start();
        $link->render(new SpyEmitter());
        ob_end_clean();

        self::assertSame(['HTTP/1.1 200 Ok', 'Content-Type: application/json', 'X-A: 1'], $link->headers);
    }

    public function testRenderWithInvalidUtf8Throws(): void
    {
        $Response = new Response();
        $Response->setPayload("\xB1");
        $Response->setHeader('X-A: 1');
        $link = new CapturingJson200(new Request('/x'), $Response);

        ob_start();
        try {
            $link->render(new SpyEmitter());
            self::fail('Expected a JsonException');
        } catch (\JsonException) {
            // expected
        } finally {
            $output = ob_get_clean();
        }

        self::assertSame([], $link->headers);
        self::assertSame('', $output);
    }

    #[RunInSeparateProcess]
    public function testSendHeaderCallsHeader(): void
    {
        $link = new PayloadToJson200(new Request('/x'), new Response());

        ob_start();
        $link->render(new SpyEmitter());
        $output = ob_get_clean();

        self::assertSame('{}', $output);
    }
}

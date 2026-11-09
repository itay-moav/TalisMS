<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use Talis\Doors\CliEmbedded;
use Talis\Message\Renderers\Cli as CliRenderer;
use Talis\Message\ResponseType;
use Talis\Tests\Fixtures\Doors\RecordingCliEmbedded;

final class CliEmbeddedTest extends DoorTestCase
{
    private function door(): RecordingCliEmbedded
    {
        return new RecordingCliEmbedded($this->emitter);
    }

    public function testDefaultEmitterIsCliRenderer(): void
    {
        self::assertInstanceOf(CliRenderer::class, (new \ReflectionProperty(CliEmbedded::class, 'emitter'))->getValue(new CliEmbedded()));
    }

    public function testGogogoPassesBodyAndReturnsResponse(): void
    {
        $body = (object) ['params' => (object) ['a' => 1]];
        $door = $this->door();

        $Response = $door->gogogo('/fix/ping/echo', $body);

        self::assertSame($body, $door->seenBody);
        self::assertSame($body, self::payloadBody($Response));
        self::assertSame(['', 'fix', 'ping', 'echo'], $door->seenParts);
        self::assertSame('/fix/ping/echo', $door->seenUri);
    }

    public function testCustomEmitterReceivesResponse(): void
    {
        $Response = $this->door()->gogogo('/fix/ping/echo', new \stdClass());

        self::assertSame($Response, $this->emittedOnce());
    }

    public function testUnknownRouteReturns404(): void
    {
        $Response = $this->door()->gogogo('/no/such/route', new \stdClass());

        self::assertSame(404, self::code($Response));
        self::assertSame(ResponseType::Error, $Response->getResponseType());
    }

    public function testExceptionReturns500(): void
    {
        $Response = $this->door()->gogogo('/fix/ping/boom', new \stdClass());

        self::assertSame(500, self::code($Response));
        self::assertStringContainsString('boom from fixture', $Response->getMessage());
        self::assertCount(1, $this->logged('fatal'));
    }

    public function testDebugLogsBody(): void
    {
        $body = (object) ['x' => 1];

        $this->door()->gogogo('/fix/ping/echo', $body);

        $debug = $this->logged('debug');
        self::assertContains('Embedded CLI body', $debug);
        self::assertContains($body, $debug);
    }
}

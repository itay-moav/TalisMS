<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use PHPUnit\Framework\Attributes\DataProvider;
use Talis\Message\ResponseType;
use Talis\Message\Status\Code404;
use Talis\Tests\Fixtures\Doors\ExposedDoor;

final class aDoorTest extends DoorTestCase
{
    private function door(): ExposedDoor
    {
        return new ExposedDoor($this->emitter);
    }

    /**
     * @return list<string>
     */
    private static function parts(string $uri): array
    {
        return explode('/', $uri);
    }

    public function testDispatchRendersFinalLinkWithInjectedEmitter(): void
    {
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/echo'), null, '/fix/ping/echo');

        self::assertSame($Response, $this->emittedOnce());
        self::assertSame(200, self::code($Response));
    }

    public function testDispatchReturnsFinalLinkResponse(): void
    {
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/read'), null, '/fix/ping/read');

        self::assertSame($this->emittedOnce(), $Response);
        self::assertSame(ResponseType::Response, $Response->getResponseType());
    }

    public function testDispatchReturnsTheEmittedResponseWhenLinkEmitsAnotherOne(): void
    {
        // HasBody renders a new Response, not the chain one
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/needsbody'), null, '/fix/ping/needsbody');

        self::assertSame($this->emittedOnce(), $Response);
        self::assertSame(ResponseType::Dependency, $Response->getResponseType());
        self::assertSame(400, self::code($Response));
    }

    public function testDispatchReturnsChainResponseWhenNothingWasEmitted(): void
    {
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/silent'), null, '/fix/ping/silent');

        self::assertSame([], $this->emitter->emitted);
        self::assertSame(202, self::code($Response));
    }

    public function testDispatchPassesPartsBodyAndUriToRequest(): void
    {
        $body = (object) ['params' => (object) ['a' => 1]];

        $Response = $this->door()->callDispatch(self::parts('/fix/ping/echo/id/5'), $body, '/fix/ping/echo/id/5?x=1');

        $payload = $Response->getPayload();
        self::assertInstanceOf(\stdClass::class, $payload);
        self::assertSame('/fix/ping/echo/id/5?x=1', $payload->uri);
        self::assertSame($body, $payload->body);
    }

    public function testDispatchCatchesThrowable(): void
    {
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/boom'), null, '/fix/ping/boom');

        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertSame(500, self::code($Response));
        self::assertSame($Response, $this->emittedOnce());
        $fatal = end($this->logger->calls);
        self::assertIsArray($fatal);
        self::assertSame('fatal', $fatal['level']);
        self::assertInstanceOf(\RuntimeException::class, $fatal['inp']);
        self::assertTrue($fatal['full_stack']);
    }

    public function testDispatchMapsParamNotFoundTo400(): void
    {
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/missingparam'), null, '/fix/ping/missingparam');

        self::assertSame(400, self::code($Response));
        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertSame('Param [nope] not found', $Response->getMessage());
        self::assertSame($Response, $this->emittedOnce());
        self::assertSame([], $this->logged('fatal'));
    }

    public function testDispatchMapsInvalidParamTo400(): void
    {
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/badparam'), null, '/fix/ping/badparam');

        self::assertSame(400, self::code($Response));
        self::assertSame('Param [id] is not good', $Response->getMessage());
        self::assertSame($Response, $this->emittedOnce());
    }

    public function testRenderThatThrowsEndsAsEmitted500(): void
    {
        ob_start();
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/inf'), null, '/fix/ping/inf');
        $output = ob_get_clean();

        self::assertSame('', $output);
        self::assertSame(500, self::code($Response));
        self::assertSame($Response, $this->emittedOnce());
    }

    public function testExceptionWithInvalidUtf8MessageStillEmits500(): void
    {
        $Response = $this->door()->callDispatch(self::parts('/fix/ping/badutf8'), null, '/fix/ping/badutf8');

        self::assertSame(500, self::code($Response));
        self::assertTrue(mb_check_encoding($Response->getMessage(), 'UTF-8'));
        self::assertStringContainsString('bad ', $Response->getMessage());
        // the scrubbed message can be JSON encoded
        self::assertJson((string) $Response);
    }

    public function testExceptionResponseMessageIsExceptionString(): void
    {
        $e = new \RuntimeException('oops');

        $Response = $this->door()->callExceptionResponse($e);

        self::assertSame((string) $e, $Response->getMessage());
        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertSame(500, self::code($Response));
        self::assertSame([], $this->emitter->emitted);
    }

    public function testBadInputEmits400(): void
    {
        $Response = $this->door()->callBadInput('no good');

        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertSame(400, self::code($Response));
        self::assertSame('no good', $Response->getMessage());
        self::assertSame($Response, $this->emittedOnce());
        $this->assertLoggedWith('warning', 'no good', false);
    }

    public function testBadInputScrubsInvalidUtf8(): void
    {
        $Response = $this->door()->callBadInput("bad \xFF");

        self::assertTrue(mb_check_encoding($Response->getMessage(), 'UTF-8'));
        self::assertJson((string) $Response);
    }

    public function testErrorResponseBuildsMarkedError(): void
    {
        $Response = $this->door()->callErrorResponse(new Code404(), 'gone');

        self::assertSame(404, self::code($Response));
        self::assertSame('gone', $Response->getMessage());
        self::assertSame(ResponseType::Error, $Response->getResponseType());
    }

    public function testUriPartsStripsQueryAndTrailingSlash(): void
    {
        self::assertSame(['', 'a', 'b', 'c'], $this->door()->callUriParts('/a/b/c/?x=1'));
    }

    public function testUriPartsPlain(): void
    {
        self::assertSame(['', 'a', 'b', 'c', 'id', '5'], $this->door()->callUriParts('/a/b/c/id/5'));
    }

    public function testUriPartsRootOnly(): void
    {
        self::assertSame([''], $this->door()->callUriParts('/'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function emptyBodies(): array
    {
        return ['empty' => [''], 'whitespace' => ["  \n"]];
    }

    #[DataProvider('emptyBodies')]
    public function testDecodeJsonObjectEmptyIsNull(string $raw): void
    {
        self::assertNull($this->door()->callDecodeJsonObject($raw));
    }

    public function testDecodeJsonObjectNullLiteralIsNull(): void
    {
        self::assertNull($this->door()->callDecodeJsonObject('null'));
    }

    public function testDecodeJsonObjectReturnsObject(): void
    {
        $body = $this->door()->callDecodeJsonObject('{"params":{"a":1}}');

        self::assertInstanceOf(\stdClass::class, $body);
        self::assertInstanceOf(\stdClass::class, $body->params);
        self::assertSame(1, $body->params->a);
    }

    public function testDecodeJsonObjectInvalidThrowsJsonException(): void
    {
        $this->expectException(\JsonException::class);
        $this->door()->callDecodeJsonObject('{n:1}');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonObjects(): array
    {
        return ['array' => ['[1,2]'], 'string' => ['"x"']];
    }

    #[DataProvider('nonObjects')]
    public function testDecodeJsonObjectArrayThrows(string $raw): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Request body must be a JSON object');
        $this->door()->callDecodeJsonObject($raw);
    }
}

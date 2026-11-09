<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use PHPUnit\Framework\Attributes\DataProvider;
use Talis\Doors\HTTP;
use Talis\Message\Renderers\HTTP as HTTPRenderer;
use Talis\Message\ResponseType;
use Talis\Tests\Fixtures\Doors\RecordingHTTP;

final class HTTPTest extends DoorTestCase
{
    private function door(bool $showExceptions = false): RecordingHTTP
    {
        return new RecordingHTTP($this->emitter, $showExceptions);
    }

    public function testDefaultEmitterIsHttpRenderer(): void
    {
        $door = new HTTP();

        self::assertInstanceOf(HTTPRenderer::class, (new \ReflectionProperty(HTTP::class, 'emitter'))->getValue($door));
        self::assertFalse((new \ReflectionProperty(HTTP::class, 'showExceptions'))->getValue($door));
    }

    public function testGogogoUsesInjectedEmitter(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/echo';

        $Response = $this->door()->gogogo('');

        self::assertSame($Response, $this->emittedOnce());
        self::assertSame(200, self::code($Response));
    }

    public function testRootUriIsStripped(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/x/y/z/id/5';
        $door = $this->door();

        $door->gogogo('/api');

        self::assertSame(['', 'x', 'y', 'z', 'id', '5'], $door->seenParts);
        self::assertSame('/x/y/z/id/5', $door->seenUri);
    }

    public function testRootUriNotAPrefixLeavesUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/other/x/y/z';
        $door = $this->door();

        $door->gogogo('/api');

        self::assertSame(['', 'other', 'x', 'y', 'z'], $door->seenParts);
        self::assertSame('/other/x/y/z', $door->seenUri);
    }

    public function testUriOutsideRootIs404(): void
    {
        // left as is, so it does not match a route; no crash, no 500
        $_SERVER['REQUEST_URI'] = '/other/fix/ping/echo';

        $Response = $this->door()->gogogo('/api');

        self::assertSame(404, self::code($Response));
        self::assertSame(ResponseType::Error, $Response->getResponseType());
    }

    public function testEmptyRootUriUsesWholeUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/x/y/z';
        $door = $this->door();

        $door->gogogo('');

        self::assertSame('/api/x/y/z', $door->seenUri);
    }

    public function testRootUriOnlyAtStart(): void
    {
        $_SERVER['REQUEST_URI'] = '/x/api/y/z';
        $door = $this->door();

        $door->gogogo('/api');

        self::assertSame(['', 'x', 'api', 'y', 'z'], $door->seenParts);
        self::assertSame('/x/api/y/z', $door->seenUri);
    }

    public function testQueryStringRemovedFromPartsKeptInFullUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/x/y/z?a=1';
        $door = $this->door();

        $door->gogogo('');

        self::assertSame(['', 'x', 'y', 'z'], $door->seenParts);
        self::assertSame('/x/y/z?a=1', $door->seenUri);
    }

    public function testMissingRequestUriDefaultsToSlash(): void
    {
        unset($_SERVER['REQUEST_URI']);
        $door = $this->door();

        $door->gogogo('');

        self::assertSame([''], $door->seenParts);
        self::assertSame('/', $door->seenUri);
    }

    public function testNonStringServerValueUsesDefault(): void
    {
        $_SERVER['REQUEST_URI'] = 5;
        $door = $this->door();

        $door->gogogo('');

        self::assertSame([''], $door->seenParts);
    }

    public function testJsonBodyReachesRequest(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/echo';
        $door = $this->door();
        $door->input = '{"params":{"a":1}}';

        $Response = $door->gogogo('');

        $body = self::payloadBody($Response);
        self::assertInstanceOf(\stdClass::class, $body);
        self::assertInstanceOf(\stdClass::class, $body->params);
        self::assertSame(1, $body->params->a);
    }

    public function testEmptyBodyIsNull(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/echo';
        $door = $this->door();

        $door->gogogo('');

        self::assertSame(1, $door->dispatchCalls);
        self::assertNull($door->seenBody);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function formContentTypes(): array
    {
        return [
            'urlencoded' => ['application/x-www-form-urlencoded; charset=UTF-8'],
            'multipart' => ['multipart/form-data; boundary=x'],
            'upper case' => ['Application/X-WWW-Form-Urlencoded'],
        ];
    }

    #[DataProvider('formContentTypes')]
    public function testFormPostBodyIsNotDecoded(string $contentType): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/echo';
        $_SERVER['CONTENT_TYPE'] = $contentType;
        $door = $this->door();
        $door->input = 'a=1&b=2';

        $Response = $door->gogogo('');

        self::assertSame(200, self::code($Response));
        self::assertNull($door->seenBody);
        self::assertSame(0, $door->readInputCalls);
    }

    public function testJsonContentTypeStillDecoded(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/echo';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $door = $this->door();
        $door->input = '{"a":2}';

        $door->gogogo('');

        self::assertInstanceOf(\stdClass::class, $door->seenBody);
        self::assertSame(2, $door->seenBody->a);
    }

    public function testInvalidJsonReturns400(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/echo';
        $door = $this->door();
        $door->input = '{n:1}';

        $Response = $door->gogogo('');

        self::assertSame(400, self::code($Response));
        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertStringStartsWith('Invalid JSON body', $Response->getMessage());
        self::assertSame($Response, $this->emittedOnce());
        self::assertSame(0, $door->dispatchCalls);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonJsonBodies(): array
    {
        return [
            'form data without form type' => ['a=1&b=2'],
            'json array' => ['[1]'],
            'json string' => ['"x"'],
        ];
    }

    #[DataProvider('nonJsonBodies')]
    public function testNonJsonBodyIs400(string $input): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/echo';
        $_SERVER['CONTENT_TYPE'] = 'text/plain';
        $door = $this->door();
        $door->input = $input;

        $Response = $door->gogogo('');

        self::assertSame(400, self::code($Response));
        self::assertSame(0, $door->dispatchCalls);
    }

    public function testExceptionHiddenByDefault(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/boom';

        $Response = $this->door()->gogogo('');

        self::assertSame(500, self::code($Response));
        self::assertSame('An error has occurred.', $Response->getMessage());
        $payload = $Response->getPayload();
        self::assertInstanceOf(\stdClass::class, $payload);
        self::assertFalse(property_exists($payload, 'TalisErrorStack'));
        self::assertSame($Response, $this->emittedOnce());
    }

    public function testShowExceptionsAddsStack(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/boom';

        $Response = $this->door(showExceptions: true)->gogogo('');

        self::assertSame(500, self::code($Response));
        self::assertStringContainsString('boom from fixture', $Response->getMessage());
        $payload = $Response->getPayload();
        self::assertInstanceOf(\stdClass::class, $payload);
        $stack = $payload->TalisErrorStack;
        self::assertIsArray($stack);
        self::assertTrue(array_is_list($stack));
        self::assertNotSame([], $stack);
        self::assertContainsOnlyString($stack);
    }

    public function testShowExceptionsScrubsInvalidUtf8(): void
    {
        $_SERVER['REQUEST_URI'] = '/fix/ping/badutf8';

        $Response = $this->door(showExceptions: true)->gogogo('');

        self::assertSame(500, self::code($Response));
        self::assertJson((string) $Response);
    }

    public function testReadInputReadsPhpInput(): void
    {
        $method = new \ReflectionMethod(HTTP::class, 'readInput');

        self::assertSame('', $method->invoke(new HTTP($this->emitter)));
    }

    public function testDebugLogsRequestStartAndRootUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/fix/ping/echo';

        $this->door()->gogogo('/api');

        $debug = $this->logged('debug');
        self::assertContains('Request log starts here', $debug);
        self::assertContains('Root URI [/api]', $debug);
    }
}

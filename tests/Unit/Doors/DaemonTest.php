<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use PHPUnit\Framework\Attributes\DataProvider;
use Talis\Doors\Daemon;
use Talis\Message\Renderers\Log;
use Talis\Message\ResponseType;
use Talis\Tests\Fixtures\Doors\RecordingDaemon;

final class DaemonTest extends DoorTestCase
{
    private function door(): RecordingDaemon
    {
        return new RecordingDaemon($this->emitter);
    }

    public function testDefaultEmitterIsLog(): void
    {
        self::assertInstanceOf(Log::class, (new \ReflectionProperty(Daemon::class, 'emitter'))->getValue(new Daemon()));
    }

    public function testBodyIsFileMinusUrl(): void
    {
        $door = $this->door();

        $door->gogogo('{"url":"/fix/ping/read","params":{"a":1}}');

        self::assertInstanceOf(\stdClass::class, $door->seenBody);
        self::assertFalse(property_exists($door->seenBody, 'url'));
        self::assertInstanceOf(\stdClass::class, $door->seenBody->params);
        self::assertSame(1, $door->seenBody->params->a);
    }

    public function testBodyKeepsEveryOtherKey(): void
    {
        $door = $this->door();

        $door->gogogo('{"url":"/a/b/c","params":{"x":1},"meta":2}');

        self::assertInstanceOf(\stdClass::class, $door->seenBody);
        self::assertSame(['params', 'meta'], array_keys(get_object_vars($door->seenBody)));
        self::assertSame(2, $door->seenBody->meta);
    }

    public function testUrlOnlyGivesEmptyBody(): void
    {
        $door = $this->door();

        $door->gogogo('{"url":"/a/b/c"}');

        self::assertEquals(new \stdClass(), $door->seenBody);
    }

    public function testUrlSplitIntoParts(): void
    {
        $door = $this->door();

        $door->gogogo('{"url":"/a/b/c/id/5"}');

        self::assertSame(['', 'a', 'b', 'c', 'id', '5'], $door->seenParts);
        self::assertSame('/a/b/c/id/5', $door->seenUri);
    }

    public function testReturnsFinalLinkResponse(): void
    {
        $Response = $this->door()->gogogo('{"url":"/fix/ping/echo","params":{"a":1}}');

        self::assertSame($Response, $this->emittedOnce());
        self::assertSame(200, self::code($Response));
        self::assertSame(ResponseType::Response, $Response->getResponseType());
    }

    /**
     * @return array<string, array{string, non-empty-string}>
     */
    public static function badMessages(): array
    {
        return [
            'invalid json' => ['{bad', 'Invalid JSON message'],
            'empty string' => ['', 'Message is empty'],
            'null literal' => ['null', 'Message is empty'],
            'json array' => ['[1]', 'Invalid JSON message'],
            'non string url' => ['{"url":5}', 'Message has no url'],
            'empty url' => ['{"url":""}', 'Message has no url'],
            'base64 input' => [base64_encode('{"url":"/fix/ping/echo"}'), 'Invalid JSON message'],
        ];
    }

    /**
     * @param non-empty-string $messageStart
     */
    #[DataProvider('badMessages')]
    public function testBadMessageIs400(string $raw, string $messageStart): void
    {
        $door = $this->door();

        $Response = $door->gogogo($raw);

        self::assertSame(400, self::code($Response));
        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertStringStartsWith($messageStart, $Response->getMessage());
        self::assertSame($Response, $this->emittedOnce());
        self::assertSame(0, $door->dispatchCalls);
    }

    public function testMissingUrlIs400(): void
    {
        $door = $this->door();

        $Response = $door->gogogo('{"params":{}}');

        self::assertSame(400, self::code($Response));
        self::assertSame('Message has no url', $Response->getMessage());
        self::assertSame(0, $door->dispatchCalls);
    }

    public function testExceptionReturns500(): void
    {
        $Response = $this->door()->gogogo('{"url":"/fix/ping/boom"}');

        self::assertSame(500, self::code($Response));
        self::assertCount(1, $this->logged('fatal'));
    }

    public function testDebugLogsRawMessage(): void
    {
        $raw = '{"url":"/fix/ping/echo"}';

        $this->door()->gogogo($raw);

        $debug = $this->logged('debug');
        self::assertContains('Spool message received', $debug);
        self::assertContains($raw, $debug);
    }
}

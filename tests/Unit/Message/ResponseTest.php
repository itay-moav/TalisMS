<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Message;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Message\Status\Code404;
use Talis\Message\Status\Code500;

final class ResponseTest extends TestCase
{
    public function testNewResponseDefaults(): void
    {
        $response = new Response();

        self::assertSame([], $response->getHeaders());
        self::assertSame('', $response->getMessage());
        self::assertSame(ResponseType::Response, $response->getResponseType());
        self::assertFalse($response->hasStatus());
    }

    public function testGetStatusWithoutStatusReturnsCode500(): void
    {
        $response = new Response();

        $status = $response->getStatus();

        self::assertInstanceOf(Code500::class, $status);
        self::assertSame(500, $status->getCode());
        self::assertFalse($response->hasStatus());
    }

    public function testGetStatusWithoutStatusIsCode500AndNotStored(): void
    {
        $response = new Response();

        $first = $response->getStatus();
        $second = $response->getStatus();

        self::assertNotSame($first, $second);
        self::assertFalse($response->hasStatus());
    }

    public function testSetStatusStoresAndReturnsStatus(): void
    {
        $response = new Response();
        $status = new Code404();

        $returned = $response->setStatus($status);

        self::assertSame($status, $returned);
        self::assertSame($status, $response->getStatus());
        self::assertTrue($response->hasStatus());
    }

    public function testSetHeaderAppendsInOrderAndReturnsSameInstance(): void
    {
        $response = new Response();

        $returned = $response->setHeader('A: 1')->setHeader('B: 2');

        self::assertSame(['A: 1', 'B: 2'], $response->getHeaders());
        self::assertSame($response, $returned);
    }

    public function testSetMessageReturnsAndStoresMessage(): void
    {
        $response = new Response();

        self::assertSame('hi', $response->setMessage('hi'));
        self::assertSame('hi', $response->getMessage());
    }

    public function testSetPayloadReturnsPayload(): void
    {
        $response = new Response();

        $returned = $response->setPayload(['a' => 1]);

        self::assertSame(['a' => 1], $returned);
        self::assertSame(['a' => 1], $response->getPayload());
    }

    public function testGetPayloadCreatesStdClassOnlyOnceWhenNull(): void
    {
        $response = new Response();

        $first = $response->getPayload();
        $second = $response->getPayload();

        self::assertInstanceOf(\stdClass::class, $first);
        self::assertSame($first, $second);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function falsyPayloads(): iterable
    {
        yield 'zero int' => [0];
        yield 'zero float' => [0.0];
        yield 'empty string' => [''];
        yield 'numeric string zero' => ['0'];
        yield 'false' => [false];
        yield 'empty array' => [[]];
    }

    #[DataProvider('falsyPayloads')]
    public function testGetPayloadKeepsFalsyValues(mixed $value): void
    {
        $response = new Response();

        $response->setPayload($value);

        self::assertSame($value, $response->getPayload());
    }

    public function testFalsyPayloadIsKept(): void
    {
        $response = new Response();

        $response->setPayload(0);

        self::assertSame(0, $response->getPayload());
    }

    public function testMarkMethodsSetType(): void
    {
        $response = new Response();

        $response->markError();
        self::assertSame(ResponseType::Error, $response->getResponseType());

        $response->markDependency();
        self::assertSame(ResponseType::Dependency, $response->getResponseType());

        $response->markResponse();
        self::assertSame(ResponseType::Response, $response->getResponseType());
    }

    public function testGetBodyBuildsFromParts(): void
    {
        $response = new Response();
        $response->setStatus(new Code404());
        $response->markError();
        $response->setMessage('m');
        $response->setPayload((object) ['x' => 1]);

        $body = $response->getBody();

        self::assertSame('404 Not Found', $body->status);
        self::assertSame('error', $body->type);
        self::assertSame('m', $body->message);
        self::assertInstanceOf(\stdClass::class, $body->payload);
        self::assertSame(1, $body->payload->x);
    }

    public function testGetBodyReturnsNewObjectEachCall(): void
    {
        $response = new Response();

        $first = $response->getBody();
        $second = $response->getBody();

        self::assertEquals($first, $second);
        self::assertNotSame($first, $second);
    }

    public function testGetBodyWithoutStatusReports500(): void
    {
        $response = new Response();

        $body = $response->getBody();

        self::assertSame('500 Server Error', $body->status);
        self::assertSame('response', $body->type);
    }

    public function testGetBodyDefaultsPayloadToEmptyStdClass(): void
    {
        $response = new Response();

        $body = $response->getBody();

        self::assertEquals(new \stdClass(), $body->payload);
    }

    /**
     * Keys match v4's wire shape, built from status/type/message/payload.
     */
    public function testGetBodyShape(): void
    {
        $response = new Response();

        $body = $response->getBody();

        self::assertSame(['status', 'type', 'message', 'payload'], array_keys(get_object_vars($body)));
    }

    public function testToStringIsJsonOfBody(): void
    {
        $response = new Response();
        $response->setMessage('hello');

        self::assertSame(json_encode($response->getBody()), (string) $response);
    }

    public function testToStringThrowsOnUnencodablePayload(): void
    {
        $response = new Response();
        $response->setPayload(INF);

        $this->expectException(\JsonException::class);

        (string) $response;
    }
}

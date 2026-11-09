<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use PHPUnit\Framework\Attributes\DataProvider;
use Talis\Doors\Cli;
use Talis\Message\Renderers\Cli as CliRenderer;
use Talis\Message\ResponseType;
use Talis\Tests\Fixtures\Doors\RecordingCli;

final class CliTest extends DoorTestCase
{
    private function door(): RecordingCli
    {
        return new RecordingCli($this->emitter);
    }

    public function testDefaultEmitterIsCliRenderer(): void
    {
        self::assertInstanceOf(CliRenderer::class, (new \ReflectionProperty(Cli::class, 'emitter'))->getValue(new Cli()));
    }

    public function testSuccessReturnsExitOk(): void
    {
        self::assertSame(Cli::EXIT_OK, $this->door()->gogogo('/fix/ping/echo'));
        self::assertSame(ResponseType::Response, $this->emittedOnce()->getResponseType());
        self::assertSame(200, self::code($this->emitter->last()));
    }

    public function testErrorResponseReturnsExitError(): void
    {
        self::assertSame(Cli::EXIT_ERROR, $this->door()->gogogo('/no/such/route'));
        self::assertSame(404, self::code($this->emittedOnce()));
    }

    public function testDependencyResponseReturnsExitError(): void
    {
        self::assertSame(Cli::EXIT_ERROR, $this->door()->gogogo('/fix/ping/needsbody'));
        self::assertSame(ResponseType::Dependency, $this->emittedOnce()->getResponseType());
    }

    /**
     * @return array<string, array{?string}>
     */
    public static function noBodies(): array
    {
        return ['null' => [null], 'empty' => ['']];
    }

    #[DataProvider('noBodies')]
    public function testNullAndEmptyBodyGiveNullRequestBody(?string $rawBody): void
    {
        $door = $this->door();

        self::assertSame(Cli::EXIT_OK, $door->gogogo('/fix/ping/echo', $rawBody));
        self::assertSame(1, $door->dispatchCalls);
        self::assertNull($door->seenBody);
    }

    public function testJsonBodyDecoded(): void
    {
        $door = $this->door();

        $door->gogogo('/fix/ping/echo', '{"params":{"a":1}}');

        self::assertInstanceOf(\stdClass::class, $door->seenBody);
        self::assertInstanceOf(\stdClass::class, $door->seenBody->params);
        self::assertSame(1, $door->seenBody->params->a);
    }

    public function testBase64BodyDecoded(): void
    {
        $door = $this->door();

        self::assertSame(Cli::EXIT_OK, $door->gogogo('/fix/ping/echo', base64_encode('{"params":{"a":1}}'), true));

        self::assertInstanceOf(\stdClass::class, $door->seenBody);
        self::assertInstanceOf(\stdClass::class, $door->seenBody->params);
        self::assertSame(1, $door->seenBody->params->a);
    }

    public function testBase64FlagWithEmptyBodyIsNull(): void
    {
        $door = $this->door();

        self::assertSame(Cli::EXIT_OK, $door->gogogo('/fix/ping/echo', '', true));
        self::assertNull($door->seenBody);
    }

    public function testBadBase64Is400AndExit1(): void
    {
        $door = $this->door();

        self::assertSame(Cli::EXIT_ERROR, $door->gogogo('/fix/ping/echo', '%%%', true));

        $Response = $this->emittedOnce();
        self::assertSame(400, self::code($Response));
        self::assertStringContainsString('base64', $Response->getMessage());
        self::assertSame(0, $door->dispatchCalls);
    }

    public function testInvalidJsonReturns400(): void
    {
        $door = $this->door();

        self::assertSame(Cli::EXIT_ERROR, $door->gogogo('/fix/ping/echo', '{n:1}'));
        self::assertSame(400, self::code($this->emittedOnce()));
        self::assertStringStartsWith('Invalid request body', $this->emitter->last()->getMessage());
        self::assertSame(0, $door->dispatchCalls);
    }

    public function testNonObjectJsonReturns400(): void
    {
        self::assertSame(Cli::EXIT_ERROR, $this->door()->gogogo('/fix/ping/echo', '[1]'));
        self::assertSame(400, self::code($this->emittedOnce()));
    }

    public function testExceptionReturns500WithoutExiting(): void
    {
        self::assertSame(Cli::EXIT_ERROR, $this->door()->gogogo('/fix/ping/boom'));

        $Response = $this->emittedOnce();
        self::assertSame(500, self::code($Response));
        self::assertStringContainsString('boom from fixture', $Response->getMessage());
    }

    public function testUrlTrailingSlashDropped(): void
    {
        $door = $this->door();

        $door->gogogo('/a/b/c/');

        self::assertSame(['', 'a', 'b', 'c'], $door->seenParts);
        self::assertSame('/a/b/c/', $door->seenUri);
    }
}

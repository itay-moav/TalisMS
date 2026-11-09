<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Errors;

use PHPUnit\Framework\TestCase;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Message\Status\Code409;
use Talis\Message\Status\Code422;
use Talis\TalisMain;
use Talis\Tests\Fixtures\Chain\Errors\BasicError;
use Talis\Tests\Fixtures\Chain\Errors\CustomBuildStatusError;
use Talis\Tests\Fixtures\Chain\Errors\CustomRenderError;
use Talis\Tests\Fixtures\Chain\Errors\DefaultCodeError;
use Talis\Tests\Fixtures\Chain\Errors\UnknownCodeError;
use Talis\Tests\Support\SpyEmitter;
use Talis\Tests\Support\SpyLogger;

final class aErrorTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        TalisMain::setLogger(new \Talis\commons\NullLogger());
    }

    public function testProcessReturnsSelf(): void
    {
        $error = new BasicError(new Request('/x'), new Response());

        self::assertSame($error, $error->process());
    }

    public function testRenderUsesHttpCodeStatus(): void
    {
        $Response = new Response();
        $error = new BasicError(new Request('/x'), $Response);
        $emitter = new SpyEmitter();

        $error->render($emitter);

        self::assertSame(404, $Response->getStatus()->getCode());
        self::assertSame(ResponseType::Error, $Response->getResponseType());
        self::assertSame('basic error for /x', $Response->getMessage());
        self::assertSame([$Response], $emitter->emitted);
    }

    public function testDefaultHttpCodeIs500(): void
    {
        $Response = new Response();
        $error = new DefaultCodeError(new Request('/x'), $Response);

        $error->render(new SpyEmitter());

        self::assertSame(500, $Response->getStatus()->getCode());
    }

    public function testUnknownCodeFallsBackTo500AndLogs(): void
    {
        $logger = new SpyLogger();
        TalisMain::setLogger($logger);
        $Response = new Response();
        $error = new UnknownCodeError(new Request('/x'), $Response);

        $error->render(new SpyEmitter());

        self::assertSame(500, $Response->getStatus()->getCode());
        $errors = array_column(array_filter($logger->calls, static fn (array $c): bool => $c['level'] === 'error'), 'inp');
        $matches = array_filter($errors, static fn (mixed $inp): bool => is_string($inp) && str_contains($inp, 'No status class for HTTP code 799'));
        self::assertNotEmpty($matches);
    }

    public function testRenderLogsMessage(): void
    {
        $logger = new SpyLogger();
        TalisMain::setLogger($logger);
        $error = new BasicError(new Request('/x'), new Response());

        $error->render(new SpyEmitter());

        $fullStackErrors = array_filter(
            $logger->calls,
            static fn (array $c): bool => $c['level'] === 'error' && $c['full_stack'] === true,
        );
        self::assertNotEmpty($fullStackErrors);
        self::assertSame('basic error for /x', array_values($fullStackErrors)[0]['inp']);
    }

    public function testBuildStatusCanBeOverridden(): void
    {
        $Response = new Response();
        $error = new CustomBuildStatusError(new Request('/x'), $Response);

        $error->render(new SpyEmitter());

        self::assertInstanceOf(Code409::class, $Response->getStatus());
    }

    public function testSubclassRenderOverrideWithCustomStatus(): void
    {
        $Response = new Response();
        $error = new CustomRenderError(new Request('/x'), $Response);
        $emitter = new SpyEmitter();

        $error->render($emitter);

        self::assertInstanceOf(Code422::class, $Response->getStatus());
        self::assertSame('custom render error', $Response->getMessage());
        self::assertSame([$Response], $emitter->emitted);
    }
}

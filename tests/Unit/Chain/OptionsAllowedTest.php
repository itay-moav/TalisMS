<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\OptionsAllowed;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Tests\Fixtures\Chain\GetOptionsAllowed;
use Talis\Tests\Support\SpyEmitter;

final class OptionsAllowedTest extends TestCase
{
    public function testProcessAddsAllowHeader(): void
    {
        $Response = new Response();
        $link = new OptionsAllowed(new Request('/x'), $Response);

        self::assertSame($link, $link->process());
        self::assertSame(['Allow: OPTIONS'], $Response->getHeaders());
    }

    public function testSubclassAllowedIsUsed(): void
    {
        $Response = new Response();

        (new GetOptionsAllowed(new Request('/x'), $Response))->process();

        self::assertSame(['Allow: GET, OPTIONS'], $Response->getHeaders());
    }

    public function testRenderEmits204(): void
    {
        $Response = new Response();
        $emitter = new SpyEmitter();

        (new OptionsAllowed(new Request('/x'), $Response))->render($emitter);

        $emitted = $emitter->last();
        self::assertSame($Response, $emitted);
        self::assertSame(204, $emitted->getStatus()->getCode());
        self::assertSame('Allowed Options', $emitted->getMessage());
        self::assertSame(ResponseType::Response, $emitted->getResponseType());
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\ResourceCreated;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Tests\Support\SpyEmitter;

final class ResourceCreatedTest extends TestCase
{
    public function testProcessSets201(): void
    {
        $Response = new Response();
        $link = new ResourceCreated(new Request('/x'), $Response);

        self::assertSame($link, $link->process());
        self::assertSame(201, $Response->getStatus()->getCode());
        self::assertSame('Resource created', $Response->getMessage());
        self::assertSame(ResponseType::Response, $Response->getResponseType());
    }

    public function testRenderEmitsResponse(): void
    {
        $Response = new Response();
        $link = new ResourceCreated(new Request('/x'), $Response);
        $emitter = new SpyEmitter();

        $link->render($emitter);

        self::assertSame([$Response], $emitter->emitted);
    }
}

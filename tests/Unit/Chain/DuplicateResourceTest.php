<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\DuplicateResource;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Tests\Support\SpyEmitter;

final class DuplicateResourceTest extends TestCase
{
    public function testProcessSets422Error(): void
    {
        $Response = new Response();
        $link = new DuplicateResource(new Request('/x'), $Response);

        self::assertSame($link, $link->process());
        self::assertSame(422, $Response->getStatus()->getCode());
        self::assertSame('duplicate resource', $Response->getMessage());
        self::assertSame(ResponseType::Error, $Response->getResponseType());
    }

    public function testRenderEmitsResponse(): void
    {
        $Response = new Response();
        $link = new DuplicateResource(new Request('/x'), $Response);
        $emitter = new SpyEmitter();

        $link->render($emitter);

        self::assertSame([$Response], $emitter->emitted);
    }
}

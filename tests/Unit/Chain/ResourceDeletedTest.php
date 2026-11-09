<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\ResourceDeleted;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Tests\Support\SpyEmitter;

final class ResourceDeletedTest extends TestCase
{
    public function testProcessSets204(): void
    {
        $Response = new Response();
        $link = new ResourceDeleted(new Request('/x'), $Response);

        self::assertSame($link, $link->process());
        self::assertSame(204, $Response->getStatus()->getCode());
        self::assertSame('Resource deleted', $Response->getMessage());
        self::assertSame(ResponseType::Response, $Response->getResponseType());
    }

    public function testRenderEmitsResponse(): void
    {
        $Response = new Response();
        $link = new ResourceDeleted(new Request('/x'), $Response);
        $emitter = new SpyEmitter();

        $link->render($emitter);

        self::assertSame([$Response], $emitter->emitted);
    }
}

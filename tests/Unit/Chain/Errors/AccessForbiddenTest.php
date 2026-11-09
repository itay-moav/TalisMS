<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Errors;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Errors\AccessForbidden;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;

final class AccessForbiddenTest extends TestCase
{
    public function testRendersForbidden(): void
    {
        $Response = new Response();
        $error = new AccessForbidden(new Request('/secret'), $Response);

        $error->render(new SpyEmitter());

        self::assertSame(403, $Response->getStatus()->getCode());
        self::assertStringContainsString('/secret', $Response->getMessage());
    }
}

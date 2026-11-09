<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Errors;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Errors\Unauthenticated;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;

final class UnauthenticatedTest extends TestCase
{
    public function testRendersUnauthenticated(): void
    {
        $Response = new Response();
        $error = new Unauthenticated(new Request('/private'), $Response);

        $error->render(new SpyEmitter());

        self::assertSame(401, $Response->getStatus()->getCode());
        self::assertStringContainsString('/private', $Response->getMessage());
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Errors;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Errors\ApiNotFound;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;

final class ApiNotFoundTest extends TestCase
{
    public function testRendersNotFound(): void
    {
        $Response = new Response();
        $error = new ApiNotFound(new Request('/missing'), $Response);

        $error->render(new SpyEmitter());

        self::assertSame(404, $Response->getStatus()->getCode());
        self::assertStringContainsString('/missing', $Response->getMessage());
    }
}

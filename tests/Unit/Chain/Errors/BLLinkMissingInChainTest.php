<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Errors;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Errors\BLLinkMissingInChain;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;

final class BLLinkMissingInChainTest extends TestCase
{
    public function testRendersServerError(): void
    {
        $Response = new Response();
        $error = new BLLinkMissingInChain(new Request('/x'), $Response);

        $error->render(new SpyEmitter());

        self::assertSame(500, $Response->getStatus()->getCode());
        self::assertStringContainsString('Missing BL link', $Response->getMessage());
        self::assertStringContainsString('/x', $Response->getMessage());
    }
}

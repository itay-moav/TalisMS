<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Dependencies;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Dependencies\CLIOnly;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\Dependencies\WebSapiCLIOnly;
use Talis\Tests\Support\SpyEmitter;

final class CLIOnlyTest extends TestCase
{
    public function testValidUnderCli(): void
    {
        $dependency = new CLIOnly(new Request('/x'), new Response());

        self::assertTrue((new \ReflectionMethod($dependency, 'validate'))->invoke($dependency));
    }

    public function testInvalidUnderWebSapi(): void
    {
        $dependency = new WebSapiCLIOnly(new Request('/x'), new Response());

        self::assertFalse((new \ReflectionMethod($dependency, 'validate'))->invoke($dependency));
    }

    public function testRenderEmits403Dependency(): void
    {
        $dependency = new CLIOnly(new Request('/x'), new Response());
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(403, $emitted->getStatus()->getCode());
        self::assertSame('Accessible only from the command line (cli)', $emitted->getMessage());
    }
}

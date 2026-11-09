<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Dependencies;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Dependencies\BodyFieldExist;
use Talis\Exception\InvalidChainEntry;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\Tests\Support\SpyEmitter;

final class BodyFieldExistTest extends TestCase
{
    public function testValidWhenFieldPresent(): void
    {
        $dependency = new BodyFieldExist(new Request('/x', [], (object) ['name' => 'a']), new Response(), ['field' => 'name']);

        self::assertTrue((new \ReflectionMethod($dependency, 'validate'))->invoke($dependency));
    }

    public function testInvalidWhenFieldMissing(): void
    {
        $dependency = new BodyFieldExist(new Request('/x'), new Response(), ['field' => 'name']);

        self::assertFalse((new \ReflectionMethod($dependency, 'validate'))->invoke($dependency));
    }

    public function testRenderEmits400Dependency(): void
    {
        $Response = new Response();
        $dependency = new BodyFieldExist(new Request('/x'), $Response, ['field' => 'name']);
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(400, $emitted->getStatus()->getCode());
        self::assertSame(ResponseType::Dependency, $emitted->getResponseType());
        self::assertSame('Missing field [name] in request body', $emitted->getMessage());
    }

    public function testMissingFieldParamThrows(): void
    {
        $dependency = new BodyFieldExist(new Request('/x'), new Response());

        $this->expectException(InvalidChainEntry::class);
        (new \ReflectionMethod($dependency, 'validate'))->invoke($dependency);
    }
}

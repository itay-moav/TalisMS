<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Dependencies;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Dependencies\HasBody;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;

final class HasBodyTest extends TestCase
{
    private function validate(?\stdClass $body): mixed
    {
        $dependency = new HasBody(new Request('/x', [], $body), new Response());
        return (new \ReflectionMethod($dependency, 'validate'))->invoke($dependency);
    }

    public function testStdClassParamsCounted(): void
    {
        self::assertTrue($this->validate((object) ['params' => (object) ['a' => 1]]));
        self::assertFalse($this->validate((object) ['params' => (object) []]));
    }

    public function testNonEmptyArrayParamsValid(): void
    {
        self::assertTrue($this->validate((object) ['params' => [1]]));
    }

    public function testEmptyArrayParamsInvalid(): void
    {
        self::assertFalse($this->validate((object) ['params' => []]));
    }

    public function testMissingParamsInvalid(): void
    {
        self::assertFalse($this->validate(null));
    }

    public function testScalarParamsInvalid(): void
    {
        self::assertFalse($this->validate((object) ['params' => 'x']));
    }

    public function testRenderEmits400Dependency(): void
    {
        $dependency = new HasBody(new Request('/x'), new Response());
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(400, $emitted->getStatus()->getCode());
        self::assertSame('Missing body {params: {...}} part of request', $emitted->getMessage());
    }
}

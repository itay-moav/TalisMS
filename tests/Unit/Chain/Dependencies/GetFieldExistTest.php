<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Dependencies;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Dependencies\GetFieldExist;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;

final class GetFieldExistTest extends TestCase
{
    private function validate(GetFieldExist $dependency): mixed
    {
        return (new \ReflectionMethod($dependency, 'validate'))->invoke($dependency);
    }

    public function testValidWhenPresent(): void
    {
        $dependency = new GetFieldExist(new Request('/x', ['id' => '5']), new Response(), ['field' => 'id']);

        self::assertTrue($this->validate($dependency));
    }

    public function testInvalidWhenMissing(): void
    {
        $dependency = new GetFieldExist(new Request('/x'), new Response(), ['field' => 'id']);

        self::assertFalse($this->validate($dependency));
    }

    public function testRenderEmits400Dependency(): void
    {
        $dependency = new GetFieldExist(new Request('/x'), new Response(), ['field' => 'id']);
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(400, $emitted->getStatus()->getCode());
        self::assertSame('Missing URI param [id]', $emitted->getMessage());
    }

    public function testValidateLogsValidatorDebugLine(): void
    {
        $logger = new \Talis\Tests\Support\SpyLogger();
        \Talis\TalisMain::setLogger($logger);
        try {
            $dependency = new GetFieldExist(new Request('/x', ['id' => '1']), new Response(), ['field' => 'id']);
            (new \ReflectionMethod($dependency, 'validate'))->invoke($dependency);
        } finally {
            \Talis\TalisMain::setLogger(new \Talis\commons\NullLogger());
        }

        self::assertContains(['level' => 'debug', 'inp' => 'validator ' . GetFieldExist::class, 'full_stack' => null], $logger->calls);
    }
}

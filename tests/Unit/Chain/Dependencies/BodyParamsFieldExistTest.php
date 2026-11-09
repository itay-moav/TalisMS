<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Dependencies;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Dependencies\BodyParamsFieldExist;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\TalisMain;
use Talis\Tests\Support\SpyEmitter;
use Talis\Tests\Support\SpyLogger;

final class BodyParamsFieldExistTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        TalisMain::setLogger(new \Talis\commons\NullLogger());
    }

    private function validate(BodyParamsFieldExist $dependency): mixed
    {
        return (new \ReflectionMethod($dependency, 'validate'))->invoke($dependency);
    }

    public function testValidWhenParamPresent(): void
    {
        $body = (object) ['params' => (object) ['email' => 'x']];
        $dependency = new BodyParamsFieldExist(new Request('/x', [], $body), new Response(), ['field' => 'email']);

        self::assertTrue($this->validate($dependency));
    }

    public function testInvalidWhenNoParams(): void
    {
        $dependency = new BodyParamsFieldExist(new Request('/x'), new Response(), ['field' => 'email']);

        self::assertFalse($this->validate($dependency));
    }

    public function testInvalidWhenParamMissing(): void
    {
        $body = (object) ['params' => (object) []];
        $dependency = new BodyParamsFieldExist(new Request('/x', [], $body), new Response(), ['field' => 'email']);

        self::assertFalse($this->validate($dependency));
    }

    public function testRenderEmits400AndWarns(): void
    {
        $logger = new SpyLogger();
        TalisMain::setLogger($logger);
        $dependency = new BodyParamsFieldExist(new Request('/x'), new Response(), ['field' => 'email']);
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(400, $emitted->getStatus()->getCode());
        self::assertSame(ResponseType::Dependency, $emitted->getResponseType());
        self::assertSame('Missing param [email] in request body->params', $emitted->getMessage());
        $warnings = array_filter($logger->calls, static fn (array $c): bool => $c['level'] === 'warning');
        self::assertNotEmpty($warnings);
    }
}

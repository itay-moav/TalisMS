<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Dependencies;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Dependencies\GetFieldUInt;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Support\SpyEmitter;

final class GetFieldUIntTest extends TestCase
{
    /**
     * @param array<array-key, mixed> $getParams
     */
    private function validate(array $getParams): mixed
    {
        $dependency = new GetFieldUInt(new Request('/x', $getParams), new Response(), ['field' => 'id']);
        return (new \ReflectionMethod($dependency, 'validate'))->invoke($dependency);
    }

    public function testZeroIsValid(): void
    {
        self::assertTrue($this->validate(['id' => '0']));
    }

    public function testDigitsValid(): void
    {
        self::assertTrue($this->validate(['id' => '42']));
        self::assertTrue($this->validate(['id' => '007']));
    }

    public function testNegativeInvalid(): void
    {
        self::assertFalse($this->validate(['id' => '-1']));
    }

    public function testFloatInvalid(): void
    {
        self::assertFalse($this->validate(['id' => '1.5']));
    }

    public function testLeadingPlusRejected(): void
    {
        self::assertFalse($this->validate(['id' => '+5']));
    }

    public function testSpacedInvalid(): void
    {
        self::assertFalse($this->validate(['id' => ' 5']));
        self::assertFalse($this->validate(['id' => "5\n"]));
    }

    public function testNonNumericInvalid(): void
    {
        self::assertFalse($this->validate(['id' => 'abc']));
    }

    public function testMissingIsInvalid(): void
    {
        self::assertFalse($this->validate([]));
    }

    public function testArrayValueIs400(): void
    {
        self::assertFalse($this->validate(['id' => ['oops']]));
    }

    public function testMissingIs400Dependency(): void
    {
        $dependency = new GetFieldUInt(new Request('/x'), new Response(), ['field' => 'id']);
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(400, $emitted->getStatus()->getCode());
        self::assertSame('GET param [id] is missing', $emitted->getMessage());
    }

    public function testRenderNotUintMessage(): void
    {
        $dependency = new GetFieldUInt(new Request('/x', ['id' => 'abc']), new Response(), ['field' => 'id']);
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(400, $emitted->getStatus()->getCode());
        self::assertSame('GET param [id] is not an unsigned int, it is: [abc]', $emitted->getMessage());
    }

    public function testRenderArrayValueShowsDebugType(): void
    {
        $dependency = new GetFieldUInt(new Request('/x', ['id' => ['oops']]), new Response(), ['field' => 'id']);
        $emitter = new SpyEmitter();

        $dependency->render($emitter);

        $emitted = $emitter->last();
        self::assertSame(400, $emitted->getStatus()->getCode());
        self::assertSame('GET param [id] is not an unsigned int, it is: [array]', $emitted->getMessage());
    }

    public function testValidateLogsValidatorDebugLine(): void
    {
        $logger = new \Talis\Tests\Support\SpyLogger();
        \Talis\TalisMain::setLogger($logger);
        try {
            $dependency = new GetFieldUInt(new Request('/x', ['id' => '1']), new Response(), ['field' => 'id']);
            (new \ReflectionMethod($dependency, 'validate'))->invoke($dependency);
        } finally {
            \Talis\TalisMain::setLogger(new \Talis\commons\NullLogger());
        }

        self::assertContains(['level' => 'debug', 'inp' => 'validator ' . GetFieldUInt::class, 'full_stack' => null], $logger->calls);
    }
}

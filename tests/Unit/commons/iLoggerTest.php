<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\commons;

use PHPUnit\Framework\TestCase;
use Talis\commons\iLogger;

final class iLoggerTest extends TestCase
{
    /**
     * Guards the interface's exact shape, so a consumer's own implementation
     * of iLogger keeps working. Any change here is a breaking change.
     */
    public function testSignaturesUnchanged(): void
    {
        $interface = new \ReflectionClass(iLogger::class);
        $methods = $interface->getMethods();

        self::assertCount(5, $methods);

        $byName = [];
        foreach ($methods as $method) {
            $byName[$method->getName()] = $method;
        }

        self::assertSame(['debug', 'info', 'warning', 'error', 'fatal'], array_keys($byName));

        $this->assertSingleParam($byName['debug'], 'inp', 'mixed');
        $this->assertTwoParams($byName['info'], 'inp', 'mixed', 'full_stack', 'bool');
        $this->assertTwoParams($byName['warning'], 'inp', 'mixed', 'full_stack', 'bool');
        $this->assertTwoParams($byName['error'], 'inp', 'mixed', 'full_stack', 'bool');
        $this->assertTwoParams($byName['fatal'], 'inp', 'mixed', 'full_stack', 'bool');

        foreach ($byName as $method) {
            $returnType = $method->getReturnType();
            self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
            self::assertSame('void', $returnType->getName());
        }
    }

    private function assertSingleParam(\ReflectionMethod $method, string $paramName, string $type): void
    {
        $params = $method->getParameters();
        self::assertCount(1, $params);
        $this->assertParam($params[0], $paramName, $type);
    }

    private function assertTwoParams(
        \ReflectionMethod $method,
        string $firstName,
        string $firstType,
        string $secondName,
        string $secondType
    ): void {
        $params = $method->getParameters();
        self::assertCount(2, $params);
        $this->assertParam($params[0], $firstName, $firstType);
        $this->assertParam($params[1], $secondName, $secondType);
    }

    private function assertParam(\ReflectionParameter $param, string $name, string $type): void
    {
        self::assertSame($name, $param->getName());
        self::assertFalse($param->isOptional());
        self::assertFalse($param->isDefaultValueAvailable());

        $paramType = $param->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $paramType);
        self::assertSame($type, $paramType->getName());
    }
}

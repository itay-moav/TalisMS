<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Message;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Talis\Message\aStatus;

final class StatusCodesTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<aStatus>, int, string}>
     */
    public static function codes(): iterable
    {
        yield 'Code200' => [\Talis\Message\Status\Code200::class, 200, 'Oook'];
        yield 'Code201' => [\Talis\Message\Status\Code201::class, 201, 'Resource Created'];
        yield 'Code202' => [\Talis\Message\Status\Code202::class, 202, 'Accepted'];
        yield 'Code204' => [\Talis\Message\Status\Code204::class, 204, 'No Content'];
        yield 'Code400' => [\Talis\Message\Status\Code400::class, 400, 'Bad Request'];
        yield 'Code401' => [\Talis\Message\Status\Code401::class, 401, 'Unauthorized'];
        yield 'Code403' => [\Talis\Message\Status\Code403::class, 403, 'Forbidden'];
        yield 'Code404' => [\Talis\Message\Status\Code404::class, 404, 'Not Found'];
        yield 'Code405' => [\Talis\Message\Status\Code405::class, 405, 'Method Not Allowed'];
        yield 'Code409' => [\Talis\Message\Status\Code409::class, 409, 'Conflict'];
        yield 'Code422' => [\Talis\Message\Status\Code422::class, 422, 'Unprocessable Entity'];
        yield 'Code500' => [\Talis\Message\Status\Code500::class, 500, 'Server Error'];
        yield 'Code501' => [\Talis\Message\Status\Code501::class, 501, 'Not Implemented'];
    }

    /**
     * @param class-string<aStatus> $class
     */
    #[DataProvider('codes')]
    public function testCodeAndMessage(string $class, int $expectedCode, string $expectedMsg): void
    {
        $status = new $class();

        self::assertSame($expectedCode, $status->getCode());
        self::assertSame($expectedMsg, $status->getMsg());
    }

    public function testGetCodeAndGetMsgReturnSubclassValues(): void
    {
        $status = new class extends aStatus {
            protected int $code = 418;

            protected string $msg = 'Teapot';
        };

        self::assertSame(418, $status->getCode());
        self::assertSame('Teapot', $status->getMsg());
    }

    public function testDynamicMessageIsGone(): void
    {
        self::assertFalse(method_exists(aStatus::class, 'dynamic_message'));
    }

    public function testEveryStatusFileIsCovered(): void
    {
        $files = glob(__DIR__ . '/../../../src/Talis/Message/Status/Code*.php');
        self::assertNotFalse($files);

        $classesInProvider = [];
        foreach (self::codes() as [$class]) {
            $classesInProvider[] = $class;
        }

        $classesOnDisk = [];
        foreach ($files as $file) {
            $classesOnDisk[] = 'Talis\\Message\\Status\\' . basename($file, '.php');
        }

        sort($classesInProvider);
        sort($classesOnDisk);

        self::assertSame($classesOnDisk, $classesInProvider);
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\commons;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Talis\commons\iLogger;
use Talis\commons\NullLogger;

final class NullLoggerTest extends TestCase
{
    public function testImplementsILogger(): void
    {
        $implements = class_implements(NullLogger::class);

        self::assertIsArray($implements);
        self::assertContains(iLogger::class, $implements);
    }

    public function testAllMethodsAreSilent(): void
    {
        $logger = new NullLogger();

        $this->expectOutputString('');

        $logger->debug('x');
        $logger->info('x', true);
        $logger->info('x', false);
        $logger->warning('x', true);
        $logger->warning('x', false);
        $logger->error('x', true);
        $logger->error('x', false);
        $logger->fatal('x', true);
        $logger->fatal('x', false);
    }

    #[RunInSeparateProcess]
    public function testAutoloadsOnItsOwn(): void
    {
        self::assertTrue(class_exists(NullLogger::class));
    }
}

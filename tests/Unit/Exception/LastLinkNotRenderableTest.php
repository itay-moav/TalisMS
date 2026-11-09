<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Talis\Exception\LastLinkNotRenderable;

final class LastLinkNotRenderableTest extends TestCase
{
    public function testMessageAndDefaults(): void
    {
        $e = new LastLinkNotRenderable('Api\\fixturetalisplain');

        self::assertStringContainsString('Api\\fixturetalisplain', $e->getMessage());
        self::assertStringContainsString('iRenderable', $e->getMessage());
        self::assertSame(0, $e->getCode());
        self::assertNull($e->getPrevious());
    }

    public function testPreviousIsKept(): void
    {
        $previous = new \RuntimeException('cause');

        $e = new LastLinkNotRenderable('Api\\fixturetalisplain', $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain\Filters;

use PHPUnit\Framework\TestCase;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\Recorder;
use Talis\Tests\Fixtures\Chain\RecordingFilter;

final class aFilterTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        Recorder::reset();
    }

    public function testProcessCallsFilterAndReturnsSelf(): void
    {
        $filter = new RecordingFilter(new Request('/x'), new Response());

        self::assertSame($filter, $filter->process());
        self::assertSame(['filter'], Recorder::$ran);
    }
}

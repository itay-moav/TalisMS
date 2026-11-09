<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\Filters\aFilter;
use Talis\Message\Request;

final class RecordingFilter extends aFilter
{
    #[\Override]
    public function filter(Request $Request): void
    {
        Recorder::record('filter');
    }
}

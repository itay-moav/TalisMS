<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\Cli;

final class RecordingCli extends Cli
{
    use RecordsDispatch;
}

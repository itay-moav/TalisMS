<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\Daemon;

final class RecordingDaemon extends Daemon
{
    use RecordsDispatch;
}

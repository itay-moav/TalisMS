<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\CliEmbedded;

final class RecordingCliEmbedded extends CliEmbedded
{
    use RecordsDispatch;
}

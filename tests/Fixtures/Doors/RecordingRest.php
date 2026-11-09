<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\Rest;

final class RecordingRest extends Rest
{
    use RecordsDispatch;
    use FakeInput;
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\HTTP;

final class RecordingHTTP extends HTTP
{
    use RecordsDispatch;
    use FakeInput;
}

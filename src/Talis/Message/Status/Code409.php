<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code409 extends \Talis\Message\aStatus
{
    protected int $code = 409;

    protected string $msg = 'Conflict';
}

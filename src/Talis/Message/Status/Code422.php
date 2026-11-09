<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code422 extends \Talis\Message\aStatus
{
    protected int $code = 422;

    protected string $msg = 'Unprocessable Entity';
}

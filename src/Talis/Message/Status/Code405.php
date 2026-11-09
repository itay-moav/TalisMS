<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code405 extends \Talis\Message\aStatus
{
    protected int $code = 405;

    protected string $msg = 'Method Not Allowed';
}

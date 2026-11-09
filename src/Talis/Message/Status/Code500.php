<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code500 extends \Talis\Message\aStatus
{
    protected int $code = 500;

    protected string $msg = 'Server Error';
}

<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code202 extends \Talis\Message\aStatus
{
    protected int $code = 202;

    protected string $msg = 'Accepted';
}

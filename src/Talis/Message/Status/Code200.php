<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code200 extends \Talis\Message\aStatus
{
    protected int $code = 200;

    protected string $msg = 'Oook';
}

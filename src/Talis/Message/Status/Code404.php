<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code404 extends \Talis\Message\aStatus
{
    protected int $code = 404;

    protected string $msg = 'Not Found';
}

<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code201 extends \Talis\Message\aStatus
{
    protected int $code = 201;

    protected string $msg = 'Resource Created';
}

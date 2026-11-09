<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code400 extends \Talis\Message\aStatus
{
    protected int $code = 400;

    protected string $msg = 'Bad Request';
}

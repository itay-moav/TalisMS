<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code400 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 400;

    #[\Override]
    protected string $msg = 'Bad Request';
}

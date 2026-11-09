<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code501 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 501;

    #[\Override]
    protected string $msg = 'Not Implemented';
}

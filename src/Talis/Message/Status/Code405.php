<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code405 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 405;

    #[\Override]
    protected string $msg = 'Method Not Allowed';
}

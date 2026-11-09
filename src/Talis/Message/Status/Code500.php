<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code500 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 500;

    #[\Override]
    protected string $msg = 'Server Error';
}

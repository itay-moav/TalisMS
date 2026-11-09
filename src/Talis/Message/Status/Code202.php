<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code202 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 202;

    #[\Override]
    protected string $msg = 'Accepted';
}

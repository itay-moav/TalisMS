<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code409 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 409;

    #[\Override]
    protected string $msg = 'Conflict';
}

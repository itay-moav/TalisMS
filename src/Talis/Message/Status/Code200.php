<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code200 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 200;

    #[\Override]
    protected string $msg = 'Oook';
}

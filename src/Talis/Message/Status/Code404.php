<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code404 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 404;

    #[\Override]
    protected string $msg = 'Not Found';
}

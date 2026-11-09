<?php

declare(strict_types=1);

namespace Talis\Message\Status;

class Code204 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 204;

    #[\Override]
    protected string $msg = 'No Content';
}

<?php

declare(strict_types=1);

namespace Talis\Message\Status;

/**
 * The user is logged in but may not do this.
 */
class Code403 extends \Talis\Message\aStatus
{
    #[\Override]
    protected int $code = 403;

    #[\Override]
    protected string $msg = 'Forbidden';
}

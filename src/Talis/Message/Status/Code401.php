<?php

declare(strict_types=1);

namespace Talis\Message\Status;

/**
 * The user is not logged in.
 */
class Code401 extends \Talis\Message\aStatus
{
    protected int $code = 401;

    protected string $msg = 'Unauthorized';
}

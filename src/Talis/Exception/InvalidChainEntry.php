<?php

declare(strict_types=1);

namespace Talis\Exception;

/**
 * A chain link entry (a router/chain config entry) is malformed.
 */
class InvalidChainEntry extends \Exception
{
    public function __construct(string $reason, ?\Throwable $previous = null)
    {
        parent::__construct("Bad chain link entry: {$reason}", 0, $previous);
    }
}

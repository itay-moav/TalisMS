<?php

declare(strict_types=1);

namespace Talis\Exception;

/**
 * The requested URI could not be routed.
 */
class BadUri extends \Exception
{
    public function __construct(string $uri, ?\Throwable $previous = null)
    {
        parent::__construct("Bad URI [{$uri}]", 0, $previous);
    }
}

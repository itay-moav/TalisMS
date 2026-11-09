<?php

declare(strict_types=1);

namespace Talis\Exception;

/**
 * A param was found but its value is not valid.
 */
class InvalidParam extends \Exception
{
    public function __construct(string $paramName, string $reason, ?\Throwable $previous = null)
    {
        parent::__construct("Param [{$paramName}] {$reason}", 0, $previous);
    }
}

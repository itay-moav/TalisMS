<?php

declare(strict_types=1);

namespace Talis\Exception;

/**
 * The requested param is not in the Request.
 */
class ParamNotFound extends \Exception
{
    public function __construct(string $paramName, ?\Throwable $previous = null)
    {
        parent::__construct("Param [{$paramName}] not found", 0, $previous);
    }
}

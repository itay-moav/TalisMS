<?php

declare(strict_types=1);

namespace Talis\Exception;

/**
 * The requested class does not exist.
 */
class ClassNotFound extends \Exception
{
    public function __construct(string $className, ?\Throwable $previous = null)
    {
        parent::__construct("Class [{$className}] not found", 0, $previous);
    }
}

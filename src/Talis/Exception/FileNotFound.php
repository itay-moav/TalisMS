<?php

declare(strict_types=1);

namespace Talis\Exception;

/**
 * The requested file does not exist.
 */
class FileNotFound extends \Exception
{
    public function __construct(string $file, ?\Throwable $previous = null)
    {
        parent::__construct("File [{$file}] not found", 0, $previous);
    }
}

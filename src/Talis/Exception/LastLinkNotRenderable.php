<?php

declare(strict_types=1);

namespace Talis\Exception;

/**
 * The last link in a BL chain must be iRenderable, so the Door has something to render.
 */
class LastLinkNotRenderable extends \Exception
{
    public function __construct(string $className, ?\Throwable $previous = null)
    {
        parent::__construct(
            "Last chain link [{$className}] does not implement Talis\\commons\\iRenderable",
            0,
            $previous,
        );
    }
}

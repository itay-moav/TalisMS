<?php

declare(strict_types=1);

namespace Talis\commons;

/**
 * Sends a Response out in one format (HTTP, CLI text, log).
 */
interface iEmitter
{
    public function emit(\Talis\Message\Response $message): void;
}

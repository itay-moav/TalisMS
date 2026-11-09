<?php

declare(strict_types=1);

namespace Talis\commons;

/**
 * A logger that does nothing. Use it when you don't want logging at all;
 * set it in the bootstrap with Talis\TalisMain::setLogger(new NullLogger()).
 */
final class NullLogger implements iLogger
{
    #[\Override]
    public function debug(mixed $inp): void
    {
    }

    #[\Override]
    public function info(mixed $inp, bool $full_stack): void
    {
    }

    #[\Override]
    public function warning(mixed $inp, bool $full_stack): void
    {
    }

    #[\Override]
    public function error(mixed $inp, bool $full_stack): void
    {
    }

    #[\Override]
    public function fatal(mixed $inp, bool $full_stack): void
    {
    }
}

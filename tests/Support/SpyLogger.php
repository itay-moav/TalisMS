<?php

declare(strict_types=1);

namespace Talis\Tests\Support;

use Talis\commons\iLogger;

/**
 * Test double that records every log call instead of sending it anywhere.
 */
final class SpyLogger implements iLogger
{
    /**
     * @var list<array{level: string, inp: mixed, full_stack: bool|null}>
     */
    public array $calls = [];

    #[\Override]
    public function debug(mixed $inp): void
    {
        $this->calls[] = ['level' => 'debug', 'inp' => $inp, 'full_stack' => null];
    }

    #[\Override]
    public function info(mixed $inp, bool $full_stack): void
    {
        $this->calls[] = ['level' => 'info', 'inp' => $inp, 'full_stack' => $full_stack];
    }

    #[\Override]
    public function warning(mixed $inp, bool $full_stack): void
    {
        $this->calls[] = ['level' => 'warning', 'inp' => $inp, 'full_stack' => $full_stack];
    }

    #[\Override]
    public function error(mixed $inp, bool $full_stack): void
    {
        $this->calls[] = ['level' => 'error', 'inp' => $inp, 'full_stack' => $full_stack];
    }

    #[\Override]
    public function fatal(mixed $inp, bool $full_stack): void
    {
        $this->calls[] = ['level' => 'fatal', 'inp' => $inp, 'full_stack' => $full_stack];
    }
}

<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\SpoolWorker;

/**
 * SpoolWorker whose seams the tests can script or spy on.
 */
final class ScriptedSpoolWorker extends SpoolWorker
{
    /**
     * Gets ($from, $to). Returns a bool to fake the move, or null to do the real move.
     */
    public ?\Closure $move = null;

    public bool $unreadable = false;

    /**
     * Gets ($milliseconds). Replaces the real sleep.
     */
    public ?\Closure $onSleep = null;

    /**
     * @var list<int>
     */
    public array $sleeps = [];

    public ?bool $pcntl = null;

    /**
     * When true, install/restore only count calls.
     */
    public bool $spyHandlers = false;

    public int $installCalls = 0;

    public int $restoreCalls = 0;

    #[\Override]
    protected function moveFile(string $from, string $to): bool
    {
        if ($this->move !== null) {
            $result = ($this->move)($from, $to);
            if (is_bool($result)) {
                return $result;
            }
        }
        return parent::moveFile($from, $to);
    }

    #[\Override]
    protected function readFile(string $path): ?string
    {
        return $this->unreadable ? null : parent::readFile($path);
    }

    #[\Override]
    protected function sleepMs(int $milliseconds): void
    {
        $this->sleeps[] = $milliseconds;
        if ($this->onSleep !== null) {
            ($this->onSleep)($milliseconds);
        }
    }

    #[\Override]
    protected function hasPcntl(): bool
    {
        return $this->pcntl ?? parent::hasPcntl();
    }

    #[\Override]
    protected function installSignalHandlers(): void
    {
        $this->installCalls++;
        if (!$this->spyHandlers) {
            parent::installSignalHandlers();
        }
    }

    #[\Override]
    protected function restoreSignalHandlers(): void
    {
        $this->restoreCalls++;
        if (!$this->spyHandlers) {
            parent::restoreSignalHandlers();
        }
    }

    /**
     * Calls the real seams directly.
     */
    public function realSleepMs(int $milliseconds): void
    {
        parent::sleepMs($milliseconds);
    }

    public function realMoveFile(string $from, string $to): bool
    {
        return parent::moveFile($from, $to);
    }

    public function realReadFile(string $path): ?string
    {
        return parent::readFile($path);
    }

    public function realHasPcntl(): bool
    {
        return parent::hasPcntl();
    }
}

<?php

declare(strict_types=1);

namespace Talis\Doors;

use Talis\Message\ResponseType;
use Talis\TalisMain;

/**
 * Long-running worker that feeds spool files to the Daemon door.
 *
 * Layout under $spoolDir:
 *   incoming/    producers drop *.json here. Write to x.json.tmp first and
 *                rename to x.json, so a half-written file is never read.
 *   processing/  files claimed by a worker
 *   done/        files whose final Response type was Response
 *   failed/      everything else (errors, dependency failures, bad JSON)
 *
 * A file is claimed with rename(), which is atomic on one filesystem, so
 * many workers can share one spool folder. Done and failed files keep their
 * original name.
 *
 * run() is meant for systemd or supervisord. It does no daemonizing. SIGTERM
 * and SIGINT finish the current file, then stop the loop.
 */
class SpoolWorker
{
    public const string INCOMING = 'incoming';

    public const string PROCESSING = 'processing';

    public const string DONE = 'done';

    public const string FAILED = 'failed';

    private bool $stopRequested = false;

    /**
     * One Daemon is reused for every file; it holds no per-request state.
     */
    public function __construct(
        private readonly string $spoolDir,
        private readonly Daemon $door = new Daemon(),
        private readonly int $idleSleepMs = 1000,
    ) {
        if ($spoolDir === '') {
            throw new \InvalidArgumentException('Spool folder must not be empty');
        }
        if ($idleSleepMs < 0) {
            throw new \InvalidArgumentException('Idle sleep must not be negative');
        }
    }

    /**
     * Loops until stop() is called. Sleeps only after a pass that found no work.
     */
    public function run(): void
    {
        $this->stopRequested = false;
        $this->warnAboutLeftovers();
        $this->installSignalHandlers();
        try {
            while (!$this->stopRequested) {
                $handled = $this->runOnce();
                if ($handled === 0 && !$this->isStopRequested()) {
                    $this->sleepMs($this->idleSleepMs);
                }
            }
        } finally {
            $this->restoreSignalHandlers();
        }
    }

    /**
     * One pass over incoming/. Returns how many files this worker claimed and finished.
     */
    public function runOnce(): int
    {
        $this->ensureLayout();
        $handled = 0;
        foreach ($this->listIncoming() as $incomingPath) {
            if ($this->stopRequested) {
                break;
            }
            if ($this->processFile($incomingPath)) {
                $handled++;
            }
        }
        return $handled;
    }

    /**
     * The file being processed is finished first; the loop exits before the next one.
     */
    public function stop(): void
    {
        $this->stopRequested = true;
    }

    public function isStopRequested(): bool
    {
        return $this->stopRequested;
    }

    /**
     * Full path of one spool folder. Use with the folder constants.
     */
    public function path(string $folder): string
    {
        return rtrim($this->spoolDir, '/') . '/' . $folder;
    }

    protected function ensureLayout(): void
    {
        foreach ([self::INCOMING, self::PROCESSING, self::DONE, self::FAILED] as $folder) {
            $path = $this->path($folder);
            if (!is_dir($path)) {
                @mkdir($path, 0775, true);
            }
            if (!is_dir($path)) {
                throw new \RuntimeException("Cannot create spool folder [{$path}]");
            }
        }
    }

    /**
     * @return list<string> incoming *.json files, sorted by name
     */
    protected function listIncoming(): array
    {
        $files = glob($this->path(self::INCOMING) . '/*.json') ?: [];
        sort($files);
        return $files;
    }

    /**
     * Claims, runs and files away one message.
     * Returns false when another worker claimed the file first.
     */
    protected function processFile(string $incomingPath): bool
    {
        $name = basename($incomingPath);
        $processingPath = $this->path(self::PROCESSING) . '/' . $name;

        if (!$this->moveFile($incomingPath, $processingPath)) {
            TalisMain::logger()->debug("Spool file [{$name}] was claimed by another worker");
            return false;
        }

        $failed = true;
        $raw = $this->readFile($processingPath);
        if ($raw === null) {
            TalisMain::logger()->error("Cannot read spool file [{$name}]", false);
            $result = 'unreadable';
        } else {
            try {
                $Response = $this->door->gogogo($raw);
                $failed = $Response->getResponseType() !== ResponseType::Response;
                $status = $Response->getStatus();
                $result = $status->getCode() . ' ' . $status->getMsg();
            } catch (\Throwable $e) {
                TalisMain::logger()->fatal($e, true);
                $result = 'exception';
            }
        }

        $folder = $failed ? self::FAILED : self::DONE;
        TalisMain::logger()->info("Spool file [{$name}] -> {$folder} [{$result}]", false);

        if (!$this->moveFile($processingPath, $this->path($folder) . '/' . $name)) {
            TalisMain::logger()->error("Cannot move spool file [{$name}] to {$folder}/; it stays in processing/", false);
        }
        return true;
    }

    /**
     * Atomic on one filesystem, which makes the claim safe with many workers.
     * An existing target file is overwritten.
     */
    protected function moveFile(string $from, string $to): bool
    {
        return @rename($from, $to);
    }

    protected function readFile(string $path): ?string
    {
        $content = @file_get_contents($path);
        return $content === false ? null : $content;
    }

    /**
     * With async signals a SIGTERM cuts the sleep short.
     */
    protected function sleepMs(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }

    protected function hasPcntl(): bool
    {
        return function_exists('pcntl_signal') && function_exists('pcntl_async_signals');
    }

    protected function installSignalHandlers(): void
    {
        if (!$this->hasPcntl()) {
            TalisMain::logger()->warning('pcntl is not loaded; a signal will stop the worker mid-file', false);
            return;
        }
        pcntl_async_signals(true);
        $handler = function (): void {
            $this->stop();
        };
        pcntl_signal(SIGTERM, $handler);
        pcntl_signal(SIGINT, $handler);
    }

    protected function restoreSignalHandlers(): void
    {
        if ($this->hasPcntl()) {
            pcntl_signal(SIGTERM, SIG_DFL);
            pcntl_signal(SIGINT, SIG_DFL);
        }
    }

    /**
     * Files left in processing/ mean an earlier run died mid-file. They are not moved.
     */
    private function warnAboutLeftovers(): void
    {
        $leftovers = glob($this->path(self::PROCESSING) . '/*');
        $count = $leftovers === false ? 0 : count($leftovers);
        if ($count > 0) {
            TalisMain::logger()->warning(
                "Spool has {$count} file(s) left in processing/ from an earlier run; check and move them by hand",
                false,
            );
        }
    }
}

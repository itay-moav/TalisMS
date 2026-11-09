<?php

declare(strict_types=1);

namespace Demo;

use Talis\commons\iLogger;

/**
 * Appends one line per log call to a file, for example:
 * [2026-11-09T10:00:00+00:00] INFO {"a":1}
 * $full_stack is ignored; a real app could add a trace there.
 * A failed write is ignored, so logging never breaks a request.
 */
final class DemoLogger implements iLogger
{
    public function __construct(private readonly string $filePath)
    {
    }

    #[\Override]
    public function debug(mixed $inp): void
    {
        $this->write('DEBUG', $inp);
    }

    #[\Override]
    public function info(mixed $inp, bool $full_stack): void
    {
        $this->write('INFO', $inp);
    }

    #[\Override]
    public function warning(mixed $inp, bool $full_stack): void
    {
        $this->write('WARNING', $inp);
    }

    #[\Override]
    public function error(mixed $inp, bool $full_stack): void
    {
        $this->write('ERROR', $inp);
    }

    #[\Override]
    public function fatal(mixed $inp, bool $full_stack): void
    {
        $this->write('FATAL', $inp);
    }

    private function write(string $level, mixed $inp): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = '[' . date('c') . "] {$level} " . str_replace(["\r", "\n"], ['\r', '\n'], $this->toText($inp));
        @file_put_contents($this->filePath, $line . "\n", FILE_APPEND | LOCK_EX);
    }

    private function toText(mixed $inp): string
    {
        if (is_string($inp)) {
            return $inp;
        }
        if ($inp instanceof \Throwable) {
            return $inp::class . ': ' . $inp->getMessage() . ' at ' . $inp->getFile() . ':' . $inp->getLine();
        }
        if ($inp instanceof \Stringable) {
            try {
                return (string) $inp;
            } catch (\Throwable) {
                return get_debug_type($inp);
            }
        }
        $json = json_encode($inp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        return $json === false ? get_debug_type($inp) : $json;
    }
}

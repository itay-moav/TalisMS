<?php

declare(strict_types=1);

namespace Talis\commons;

/**
 * The library emits debug/info/warning/error/fatal log calls through this
 * interface. Write a thin wrapper around whichever logger you use, implement
 * this, and set it with Talis\TalisMain::setLogger().
 */
interface iLogger
{
    /**
     * @param mixed $inp the thing to log; make sure your logger can handle its type
     */
    public function debug(mixed $inp): void;

    /**
     * @param mixed $inp the thing to log; make sure your logger can handle its type
     * @param bool $full_stack whether to add extra context (trace, stats, session, ...) if the logger supports it
     */
    public function info(mixed $inp, bool $full_stack): void;

    /**
     * @param mixed $inp the thing to log; make sure your logger can handle its type
     * @param bool $full_stack whether to add extra context (trace, stats, session, ...) if the logger supports it
     */
    public function warning(mixed $inp, bool $full_stack): void;

    /**
     * @param mixed $inp the thing to log; make sure your logger can handle its type
     * @param bool $full_stack whether to add extra context (trace, stats, session, ...) if the logger supports it
     */
    public function error(mixed $inp, bool $full_stack): void;

    /**
     * @param mixed $inp the thing to log; make sure your logger can handle its type
     * @param bool $full_stack whether to add extra context (trace, stats, session, ...) if the logger supports it
     */
    public function fatal(mixed $inp, bool $full_stack): void;
}

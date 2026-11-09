<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\Daemon;
use Talis\Message\Response;

/**
 * Daemon that records every raw message, can run a hook first, and can throw.
 */
final class SpyDaemon extends Daemon
{
    /**
     * @var list<string>
     */
    public array $seen = [];

    /**
     * Runs before the real gogogo(), with the raw message.
     */
    public ?\Closure $before = null;

    public ?\Throwable $throw = null;

    #[\Override]
    public function gogogo(string $rawJson): Response
    {
        $this->seen[] = $rawJson;
        if ($this->before !== null) {
            ($this->before)($rawJson);
        }
        if ($this->throw !== null) {
            throw $this->throw;
        }
        return parent::gogogo($rawJson);
    }
}

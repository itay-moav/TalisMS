<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * The queue of chain link entries still waiting to run (first in, first out).
 *
 * Each entry is [class name or callable, params]. The class must extend aChainLink.
 * The callable gets (Request $Request, Response $Response, array $params); it may
 * declare fewer params, for example only (Request $Request, Response $Response).
 *
 * @author Itay Moav
 * @date 2021-04-06
 *
 * @phpstan-type ChainEntry array{0: class-string<\Talis\Chain\aChainLink>|(callable(\Talis\Message\Request, \Talis\Message\Response, array<mixed>): mixed), 1: array<mixed>}
 */
class ChainContainer
{
    /**
     * Typed loosely on purpose: entries come from user config and are
     * checked when they are popped and built (see aChainLink).
     *
     * @var list<mixed>
     */
    private array $chainLinks;

    /**
     * @param list<ChainEntry> $chainLinks
     */
    public function __construct(array $chainLinks = [])
    {
        $this->chainLinks = $chainLinks;
    }

    /**
     * Takes the first entry off the queue. Its shape is checked by the caller.
     *
     * @return array<mixed>
     * @throws \UnderflowException when the queue is empty
     * @throws \Talis\Exception\InvalidChainEntry when the entry is not an array
     */
    public function pop(): array
    {
        if ($this->chainLinks === []) {
            throw new \UnderflowException('Chain container is empty');
        }
        $entry = array_shift($this->chainLinks);
        if (!is_array($entry)) {
            throw new \Talis\Exception\InvalidChainEntry('expected [class or callable, params array], got ' . get_debug_type($entry));
        }
        return $entry;
    }

    /**
     * Adds an entry at the end of the queue.
     *
     * @param ChainEntry $chainLink
     */
    public function push(array $chainLink): static
    {
        $this->chainLinks[] = $chainLink;
        return $this;
    }

    public function clear(): void
    {
        $this->chainLinks = [];
    }

    public function isEmpty(): bool
    {
        return $this->chainLinks === [];
    }

    /**
     * Logs every waiting entry.
     */
    public function debug(): void
    {
        foreach ($this->chainLinks as $i => $entry) {
            \Talis\TalisMain::logger()->debug("Chain link {$i}");
            \Talis\TalisMain::logger()->debug($entry);
        }
    }
}

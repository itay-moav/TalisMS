<?php

declare(strict_types=1);

namespace Talis\Chain;

use Talis\Exception\InvalidChainEntry;
use Talis\Message\Request;
use Talis\Message\Response;

/**
 * One step of a request chain.
 *
 * nextLinkInChain() runs this link, then the entries waiting in the shared
 * container, one after the other. The last link that runs is returned and
 * is usually the one that renders the Response.
 *
 * @author Itay Moav
 * @Date  2017-05-19
 */
abstract class aChainLink
{
    protected ChainContainer $chainContainer;

    /**
     * @param array<mixed> $params extra params some links need
     */
    public function __construct(
        protected Request $Request,
        protected Response $Response,
        protected array $params = [],
    ) {
        $this->chainContainer = new ChainContainer();
    }

    public function setChainContainer(ChainContainer $chainContainer): void
    {
        $this->chainContainer = $chainContainer;
    }

    /**
     * Adds a link at the end of the chain.
     *
     * @param class-string<aChainLink>|callable(Request, Response, array<mixed>): mixed $link
     * @param array<mixed> $params
     */
    public function appendChainLink(string|callable $link, array $params = []): static
    {
        $this->chainContainer->push([$link, $params]);
        return $this;
    }

    public function cloneResponse(): Response
    {
        return clone $this->Response;
    }

    /**
     * The link's logic. Return $this to let the chain go on.
     * Return another link to stop the chain and make that link the last one.
     */
    abstract public function process(): aChainLink;

    /**
     * Drops the rest of the chain and ends it with $NewLastChainLink
     * (an error, or a success link such as DoneSuccessful).
     */
    protected function bailOut(aChainLink&\Talis\commons\iRenderable $NewLastChainLink): aChainLink
    {
        $this->chainContainer->clear();
        return $NewLastChainLink->process();
    }

    /**
     * Runs this link and then every link left in the container.
     * Stops early when a link returns anything other than itself.
     */
    final public function nextLinkInChain(): aChainLink
    {
        $current = $this;
        while (true) {
            \Talis\TalisMain::logger()->debug('About to process: [' . $current::class . ']');
            $result = $current->process();
            if ($result !== $current || $current->chainContainer->isEmpty()) {
                return $result;
            }

            $next = $current->buildLink($current->chainContainer->pop());
            $next->setChainContainer($current->chainContainer);
            $current = $next;
        }
    }

    public function getResponse(): Response
    {
        return $this->Response;
    }

    /**
     * Reads a param that must be a string.
     *
     * @throws InvalidChainEntry when it is missing or not a string
     */
    protected function paramString(string|int $key): string
    {
        $value = $this->params[$key] ?? null;
        if (!is_string($value)) {
            throw new InvalidChainEntry('Param [' . $key . '] for [' . static::class . '] is missing or not a string');
        }
        return $value;
    }

    /**
     * Builds the next link from one container entry, using this link's
     * Request (a filter may have replaced it) and Response.
     * The checks guard against bad chain config at runtime.
     *
     * @param array<mixed> $entry
     */
    private function buildLink(array $entry): aChainLink
    {
        if (!array_key_exists(0, $entry) || !array_key_exists(1, $entry) || !is_array($entry[1])) {
            throw new InvalidChainEntry('expected [class or callable, params array]');
        }

        [$target, $params] = $entry;

        if (is_string($target) && is_a($target, aChainLink::class, true)) {
            if ((new \ReflectionClass($target))->isAbstract()) {
                throw new InvalidChainEntry("[{$target}] is abstract");
            }
            \Talis\TalisMain::logger()->debug("STARTING NEXT CHAIN LINK WITH {$target}");
            return new $target($this->Request, $this->Response, $params);
        }

        if (is_callable($target)) {
            \Talis\TalisMain::logger()->debug('STARTING NEXT CHAIN LINK WITH FUNCTION');
            return new FunctionalChainlinkWrapper($target, $this->Request, $this->Response, $params);
        }

        $what = is_string($target) ? "[{$target}]" : get_debug_type($target);
        throw new InvalidChainEntry("{$what} is not an aChainLink class or a callable");
    }
}

<?php

declare(strict_types=1);

namespace Talis\Chain;

use Talis\Message\Request;
use Talis\Message\Response;

/**
 * Runs a plain function as a chain link, so quick logic needs no class.
 *
 * Chain entry example: [function (Request $Request, Response $Response, array $params): void {...}, [params]]
 * The function may declare only (Request, Response). Its return value is ignored.
 *
 * @author Itay Moav
 * @Date  2021-04-08
 */
class FunctionalChainlinkWrapper extends aChainLink
{
    private readonly \Closure $wrappedFunction;

    /**
     * @param callable(Request, Response, array<mixed>): mixed $wrappedFunction
     * @param array<mixed> $params
     */
    public function __construct(callable $wrappedFunction, Request $Request, Response $Response, array $params = [])
    {
        parent::__construct($Request, $Response, $params);
        $this->wrappedFunction = $wrappedFunction(...);
    }

    #[\Override]
    public function process(): aChainLink
    {
        ($this->wrappedFunction)($this->Request, $this->Response, $this->params);
        return $this;
    }
}

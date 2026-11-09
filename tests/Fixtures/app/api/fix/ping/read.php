<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;
use Talis\commons\iEmitter;
use Talis\commons\iRenderable;
use Talis\Message\Request;

/**
 * Fixture API class for route /fix/ping/read. Ends the chain and renders.
 */
final class FixPingRead extends aChainLink implements iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        return $this;
    }

    #[\Override]
    public function render(iEmitter $emitter): void
    {
        $emitter->emit($this->Response);
    }

    public function getRequest(): Request
    {
        return $this->Request;
    }
}

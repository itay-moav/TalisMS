<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;
use Talis\commons\iEmitter;
use Talis\commons\iRenderable;
use Talis\Message\Status\Code200;

/**
 * Fixture for route /fix/ping/echo. A 200 whose payload holds the Request URI and body.
 */
final class FixPingEcho extends aChainLink implements iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setStatus(new Code200());
        $this->Response->markResponse();
        $this->Response->setPayload((object) ['uri' => $this->Request->getUri(), 'body' => $this->Request->getBody()]);
        return $this;
    }

    #[\Override]
    public function render(iEmitter $emitter): void
    {
        $emitter->emit($this->Response);
    }
}

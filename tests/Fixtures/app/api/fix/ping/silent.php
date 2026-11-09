<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;
use Talis\commons\iEmitter;
use Talis\commons\iRenderable;
use Talis\Message\Status\Code202;

/**
 * Fixture for route /fix/ping/silent. A 202 whose render() does not use the emitter.
 */
final class FixPingSilent extends aChainLink implements iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setStatus(new Code202());
        return $this;
    }

    #[\Override]
    public function render(iEmitter $emitter): void
    {
    }
}

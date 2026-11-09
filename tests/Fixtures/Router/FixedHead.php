<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Router;

use Talis\Chain\aChainLink;
use Talis\commons\iEmitter;
use Talis\commons\iRenderable;

/**
 * Renderable chain head returned by FixedRouter.
 */
final class FixedHead extends aChainLink implements iRenderable
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
}

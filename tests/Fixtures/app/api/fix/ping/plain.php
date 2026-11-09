<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;

/**
 * Fixture API class for route /fix/ping/plain. A chain link that is not iRenderable.
 */
final class FixPingPlain extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        return $this;
    }
}

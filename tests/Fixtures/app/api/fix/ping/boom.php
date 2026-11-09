<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;

/**
 * Fixture for route /fix/ping/boom. Throws from process().
 */
final class FixPingBoom extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        throw new \RuntimeException('boom from fixture');
    }
}

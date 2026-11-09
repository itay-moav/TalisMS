<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;
use Talis\Exception\InvalidParam;

/**
 * Fixture for route /fix/ping/badparam. Throws InvalidParam.
 */
final class FixPingBadparam extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        throw new InvalidParam('id', 'is not good');
    }
}

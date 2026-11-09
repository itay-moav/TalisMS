<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;

/**
 * Fixture for route /fix/ping/missingparam. Asks for a GET param that is not there.
 */
final class FixPingMissingparam extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Request->getParamOrFail('nope');
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;
use Talis\Chain\PayloadToJson200;

/**
 * Fixture for route /fix/ping/inf. Its payload can't be JSON encoded, so render() throws.
 */
final class FixPingInf extends PayloadToJson200
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setPayload(INF);
        return parent::process();
    }
}

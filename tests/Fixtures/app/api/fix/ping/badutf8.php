<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aChainLink;

/**
 * Fixture for route /fix/ping/badutf8. Throws with a message that is not valid UTF-8.
 */
final class FixPingBadutf8 extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        throw new \RuntimeException("bad \xB1\xFF bytes");
    }
}

<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\aFilteredValidatedChainLink;
use Talis\Chain\Dependencies\HasBody;
use Talis\Chain\DoneSuccessful;

/**
 * Fixture for route /fix/ping/needsbody. Fails its HasBody dependency when no params are sent.
 */
final class FixPingNeedsbody extends aFilteredValidatedChainLink
{
    protected array $dependencies = [[HasBody::class, []]];

    #[\Override]
    protected function getNextBl(): array
    {
        return [[DoneSuccessful::class, []]];
    }
}

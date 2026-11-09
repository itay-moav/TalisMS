<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;

/**
 * Clears the shared container, so nothing after it runs.
 */
final class ClearingLink extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('clearing');
        $this->chainContainer->clear();
        return $this;
    }
}

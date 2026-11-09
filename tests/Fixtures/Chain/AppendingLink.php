<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;

/**
 * Appends LinkC while it runs.
 */
final class AppendingLink extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('appending');
        $this->appendChainLink(LinkC::class);
        return $this;
    }
}

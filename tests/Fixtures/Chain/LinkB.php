<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;

final class LinkB extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('B');
        return $this;
    }
}

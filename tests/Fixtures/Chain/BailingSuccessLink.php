<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;
use Talis\Chain\DoneSuccessful;

final class BailingSuccessLink extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('bailing-success');
        return $this->bailOut(new DoneSuccessful($this->Request, $this->Response));
    }
}

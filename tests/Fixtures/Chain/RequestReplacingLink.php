<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;
use Talis\Message\Request;

final class RequestReplacingLink extends aChainLink
{
    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('replacing');
        $this->Request = new Request('/replaced');
        return $this;
    }
}

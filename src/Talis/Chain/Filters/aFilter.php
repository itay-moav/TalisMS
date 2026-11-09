<?php

declare(strict_types=1);

namespace Talis\Chain\Filters;

/**
 * A chain link that changes the Request before the rest of the chain sees it.
 */
abstract class aFilter extends \Talis\Chain\aChainLink implements \Talis\commons\iFilter
{
    #[\Override]
    final public function process(): \Talis\Chain\aChainLink
    {
        $this->filter($this->Request);
        return $this;
    }
}

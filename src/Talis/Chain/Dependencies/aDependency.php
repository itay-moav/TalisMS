<?php

declare(strict_types=1);

namespace Talis\Chain\Dependencies;

/**
 * A chain link that checks something about the Request. If the check
 * fails, it clears the rest of the chain and becomes the last link.
 */
abstract class aDependency extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable
{
    /**
     * Returns true when the Request passes the check.
     */
    abstract protected function validate(): bool;

    #[\Override]
    final public function process(): \Talis\Chain\aChainLink
    {
        // There must always be a BL link after a dependency. No container
        // (or an empty one) means the chain was not set up correctly.
        if ($this->chainContainer->isEmpty()) {
            return new \Talis\Chain\Errors\BLLinkMissingInChain($this->Request, new \Talis\Message\Response());
        }

        if (!$this->validate()) {
            $this->chainContainer->clear(); // stops the chain, render the current link
        }

        return $this;
    }
}

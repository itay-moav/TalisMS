<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;

/**
 * Returns an equal copy of itself, which must stop the chain.
 */
final class CloningLink extends aChainLink
{
    public static ?CloningLink $copy = null;

    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('cloning');
        self::$copy = clone $this;
        return self::$copy;
    }
}

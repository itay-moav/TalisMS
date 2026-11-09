<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;

/**
 * Returns another link, which replaces the rest of the chain.
 */
final class DivertingLink extends aChainLink
{
    public static ?LinkC $other = null;

    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('diverting');
        self::$other = new LinkC($this->Request, $this->Response);
        return self::$other;
    }
}

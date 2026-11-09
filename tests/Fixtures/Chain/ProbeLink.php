<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;
use Talis\Chain\ChainContainer;
use Talis\Message\Request;

/**
 * Exposes protected state so tests can look at it.
 */
final class ProbeLink extends aChainLink
{
    public static ?ProbeLink $last = null;

    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('probe');
        self::$last = $this;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function params(): array
    {
        return $this->params;
    }

    public function request(): Request
    {
        return $this->Request;
    }

    public function container(): ChainContainer
    {
        return $this->chainContainer;
    }

    public function readParamString(string|int $key): string
    {
        return $this->paramString($key);
    }
}

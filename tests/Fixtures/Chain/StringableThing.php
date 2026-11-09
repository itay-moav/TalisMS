<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

final class StringableThing implements \Stringable
{
    #[\Override]
    public function __toString(): string
    {
        return 'stringable';
    }
}

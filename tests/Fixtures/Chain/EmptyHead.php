<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aFilteredValidatedChainLink;

final class EmptyHead extends aFilteredValidatedChainLink
{
    #[\Override]
    protected array $filters = [];

    #[\Override]
    protected array $dependencies = [];

    #[\Override]
    protected function getNextBl(): array
    {
        return [];
    }
}

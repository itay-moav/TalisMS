<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aFilteredValidatedChainLink;

final class NoBlHead extends aFilteredValidatedChainLink
{
    protected array $filters = [];

    protected array $dependencies = [[PassingDependency::class, []]];

    #[\Override]
    protected function getNextBl(): array
    {
        return [];
    }
}

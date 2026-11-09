<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aFilteredValidatedChainLink;

final class FailingDependencyHead extends aFilteredValidatedChainLink
{
    protected array $filters = [];

    protected array $dependencies = [[FailingDependency::class, []]];

    #[\Override]
    protected function getNextBl(): array
    {
        return [[LinkA::class, []]];
    }
}

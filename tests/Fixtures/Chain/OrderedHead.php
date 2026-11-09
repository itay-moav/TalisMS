<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aFilteredValidatedChainLink;
use Talis\Chain\DoneSuccessful;

final class OrderedHead extends aFilteredValidatedChainLink
{
    protected array $filters = [[RecordingFilter::class, []]];

    protected array $dependencies = [[PassingDependency::class, []]];

    #[\Override]
    protected function getNextBl(): array
    {
        return [[LinkA::class, []], [DoneSuccessful::class, []]];
    }
}

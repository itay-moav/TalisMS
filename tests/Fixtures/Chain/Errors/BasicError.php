<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain\Errors;

use Talis\Chain\Errors\aError;

/**
 * A plain error fixture with an httpCode that has a matching status class.
 */
final class BasicError extends aError
{
    protected int $httpCode = 404;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        return 'basic error for ' . $this->Request->getUri();
    }
}

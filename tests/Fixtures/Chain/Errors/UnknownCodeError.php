<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain\Errors;

use Talis\Chain\Errors\aError;

/**
 * An error fixture whose httpCode has no matching status class.
 */
final class UnknownCodeError extends aError
{
    #[\Override]
    protected int $httpCode = 799;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        return 'unknown code error';
    }
}

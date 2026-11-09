<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain\Errors;

use Talis\Chain\Errors\aError;

/**
 * An error fixture that does not override httpCode, to check the default.
 */
final class DefaultCodeError extends aError
{
    #[\Override]
    protected function formatHumanMessage(): string
    {
        return 'default code error';
    }
}

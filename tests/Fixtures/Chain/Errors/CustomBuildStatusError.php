<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain\Errors;

use Talis\Chain\Errors\aError;
use Talis\Message\aStatus;
use Talis\Message\Status\Code409;

/**
 * An error fixture that builds its own status, the way a consumer app adds a
 * custom code (e.g. a status this library does not ship).
 */
final class CustomBuildStatusError extends aError
{
    #[\Override]
    protected int $httpCode = 0;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        return 'custom build status error';
    }

    #[\Override]
    protected function buildStatus(): aStatus
    {
        return new Code409();
    }
}

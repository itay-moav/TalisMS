<?php

declare(strict_types=1);

namespace Talis\Chain\Errors;

/**
 * User is not allowed to run this API class.
 */
class AccessForbidden extends aError
{
    #[\Override]
    protected int $httpCode = 403;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        $apiUri = $this->Request->getUri();
        return "Current user is not allowed in {$apiUri}.";
    }
}

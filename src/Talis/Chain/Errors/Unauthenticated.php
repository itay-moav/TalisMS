<?php

declare(strict_types=1);

namespace Talis\Chain\Errors;

/**
 * Unauthenticated request.
 */
class Unauthenticated extends aError
{
    protected int $httpCode = 401;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        $apiUri = $this->Request->getUri();
        return "Unauthenticated request for {$apiUri}.";
    }
}

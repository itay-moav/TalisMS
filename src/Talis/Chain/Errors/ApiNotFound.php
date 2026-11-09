<?php

declare(strict_types=1);

namespace Talis\Chain\Errors;

/**
 * API class was not found for the input route.
 */
class ApiNotFound extends aError
{
    protected int $httpCode = 404;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        $apiUri = $this->Request->getUri();
        return "Api resource for [{$apiUri}] can not be found!";
    }
}

<?php

declare(strict_types=1);

namespace Talis\Chain\Errors;

/**
 * A dependency ran out of chain: there was no BL link left to hand off to.
 */
class BLLinkMissingInChain extends aError
{
    #[\Override]
    protected int $httpCode = 500;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        $apiUri = $this->Request->getUri();
        return "Missing BL link for URI {$apiUri}. This request chain was not properly configured. The BL Link is missing, probably in the API class.";
    }
}

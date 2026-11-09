<?php

declare(strict_types=1);

namespace Talis\Chain\Filters;

/**
 * Adds the $_GET into the get params. Some consumers use this.
 * $_GET wins on a clash.
 */
class AddGetQueryStringOverriding extends aFilter
{
    #[\Override]
    public function filter(\Talis\Message\Request $Request): void
    {
        $this->Request = new \Talis\Message\Request(
            $Request->getUri(),
            array_replace($Request->getAllGetParams(), $_GET),
            $Request->getBody(),
        );

        \Talis\TalisMain::logger()->debug('FILTERED REQUEST');
        \Talis\TalisMain::logger()->debug($this->Request);
    }
}

<?php

declare(strict_types=1);

namespace Talis\Chain\Filters;

/**
 * Adds the $_POST into the get params. Some consumers use this.
 * $_POST wins on a clash.
 */
class FormPostIntoGetOverride extends aFilter
{
    #[\Override]
    public function filter(\Talis\Message\Request $Request): void
    {
        \Talis\TalisMain::logger()->debug('POST');
        \Talis\TalisMain::logger()->debug($_POST);

        $this->Request = new \Talis\Message\Request(
            $Request->getUri(),
            array_replace($Request->getAllGetParams(), $_POST),
            $Request->getBody(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Talis\Chain\Filters;

/**
 * Adds a default value to the GET params, if the field does not exist (or is '').
 */
class DefaultValueGet extends aFilter
{
    #[\Override]
    public function filter(\Talis\Message\Request $Request): void
    {
        $field = $this->paramString('field');
        $default = $this->paramString('default');

        $allGetParams = $Request->getAllGetParams();
        $value = $allGetParams[$field] ?? null;
        if ($value === null || $value === '') {
            $allGetParams[$field] = $default;
        }

        $this->Request = new \Talis\Message\Request($Request->getUri(), $allGetParams, $Request->getBody());

        \Talis\TalisMain::logger()->debug('FILTERED REQUEST');
        \Talis\TalisMain::logger()->debug($this->Request);
    }
}

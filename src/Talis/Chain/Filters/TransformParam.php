<?php

declare(strict_types=1);

namespace Talis\Chain\Filters;

/**
 * Looks for a field in the body params (params[0]). If it equals params[1]
 * it is changed to params[2].
 */
class TransformParam extends aFilter
{
    #[\Override]
    public function filter(\Talis\Message\Request $Request): void
    {
        $field = $this->paramString(0);
        $from = $this->paramString(1);
        $to = $this->paramString(2);

        $params = $Request->getBody()->params ?? null;

        \Talis\TalisMain::logger()->debug('input params for filter TransformParam');
        \Talis\TalisMain::logger()->debug($params);

        if (!$params instanceof \stdClass) {
            return;
        }

        // Loose compare on purpose: JSON may give 1 where the config has '1'.
        if (isset($params->$field) && $params->$field == $from) {
            $params->$field = $to;
        }
    }
}

<?php

declare(strict_types=1);

namespace Talis\Chain\Dependencies;

/**
 * Making sure that a get field is an unsigned integer.
 */
class GetFieldUInt extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        $field = $this->paramString('field');
        $value = $this->Request->getAllGetParams()[$field] ?? null;

        $valid = is_string($value) && preg_match('/^[0-9]+$/D', $value) === 1;

        \Talis\TalisMain::logger()->debug('validaror ' . self::class);
        \Talis\TalisMain::logger()->debug("params: [{$field}] is valid? [{$valid}]");

        return $valid;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        $field = $this->paramString('field');
        $value = $this->Request->getAllGetParams()[$field] ?? null;

        $response = new \Talis\Message\Response();
        $response->markDependency();
        if ($value === null) {
            $response->setMessage("GET param [{$field}] is missing");
        } else {
            $shown = is_string($value) ? $value : get_debug_type($value);
            $response->setMessage("GET param [{$field}] is not an unsigned int, it is: [{$shown}]");
        }
        $response->setStatus(new \Talis\Message\Status\Code400());
        $emitter->emit($response);
    }
}

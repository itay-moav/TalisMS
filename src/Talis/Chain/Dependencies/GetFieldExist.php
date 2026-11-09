<?php

declare(strict_types=1);

namespace Talis\Chain\Dependencies;

/**
 * Making sure that a get field exists in the request.
 */
class GetFieldExist extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        $field = $this->paramString('field');
        $valid = isset($this->Request->getAllGetParams()[$field]);

        \Talis\TalisMain::logger()->debug('validaror ' . self::class);
        \Talis\TalisMain::logger()->debug("params: [{$field}] is valid? [{$valid}]");

        return $valid;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        $field = $this->paramString('field');
        $response = new \Talis\Message\Response();
        $response->markDependency();
        $response->setMessage("Missing URI param [{$field}]");
        $response->setStatus(new \Talis\Message\Status\Code400());
        $emitter->emit($response);
    }
}

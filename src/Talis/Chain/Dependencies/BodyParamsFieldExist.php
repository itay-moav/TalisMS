<?php

declare(strict_types=1);

namespace Talis\Chain\Dependencies;

/**
 * Making sure that a body with a params->field was sent.
 */
class BodyParamsFieldExist extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        $field = $this->paramString('field');
        return isset($this->Request->getBody()->params->$field);
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->debug('RENDER input body');
        \Talis\TalisMain::logger()->debug($this->Request->getBody());

        $field = $this->paramString('field');
        $msg = "Missing param [{$field}] in request body->params";
        \Talis\TalisMain::logger()->warning($msg, true);

        $response = new \Talis\Message\Response();
        $response->setMessage($msg);
        $response->markDependency();
        $response->setStatus(new \Talis\Message\Status\Code400());
        $emitter->emit($response);
    }
}

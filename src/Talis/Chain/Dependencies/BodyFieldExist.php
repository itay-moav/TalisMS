<?php

declare(strict_types=1);

namespace Talis\Chain\Dependencies;

/**
 * Making sure that a body with some field was sent.
 */
class BodyFieldExist extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        $field = $this->paramString('field');
        return isset($this->Request->getBody()->$field);
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->debug('RENDER input body');
        \Talis\TalisMain::logger()->debug($this->Request->getBody());

        $field = $this->paramString('field');
        $response = new \Talis\Message\Response();
        $response->setMessage("Missing field [{$field}] in request body");
        $response->markDependency();
        $response->setStatus(new \Talis\Message\Status\Code400());
        $emitter->emit($response);
    }
}

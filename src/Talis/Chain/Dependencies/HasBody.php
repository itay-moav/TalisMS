<?php

declare(strict_types=1);

namespace Talis\Chain\Dependencies;

/**
 * Making sure that a body with params was sent.
 */
class HasBody extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        $params = $this->Request->getBody()->params ?? null;

        if ($params instanceof \stdClass) {
            return get_object_vars($params) !== [];
        }

        if (is_array($params)) {
            return $params !== [];
        }

        return false;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->debug('RENDER input body');
        \Talis\TalisMain::logger()->debug($this->Request->getBody());

        $response = new \Talis\Message\Response();
        $response->markDependency();
        $response->setMessage('Missing body {params: {...}} part of request');
        $response->setStatus(new \Talis\Message\Status\Code400());
        $emitter->emit($response);
    }
}

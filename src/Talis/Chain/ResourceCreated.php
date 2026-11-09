<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Reports a created resource (201). To return the new resource, put it in the payload in an earlier link.
 *
 * @author Itay Moav
 * @Date  2019-05-13
 */
class ResourceCreated extends aChainLink implements \Talis\commons\iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setMessage('Resource created');
        $this->Response->setStatus(new \Talis\Message\Status\Code201());
        $this->Response->markResponse();
        return $this;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->debug($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
        \Talis\TalisMain::logger()->debug('RESPONSE: ');
        \Talis\TalisMain::logger()->debug($this->Response);
        $emitter->emit($this->Response);
    }
}

<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Tells the client it tried to create a resource that already exists (422).
 *
 * @author Itay Moav
 * @Date  2023-03-02
 */
class DuplicateResource extends aChainLink implements \Talis\commons\iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setMessage('duplicate resource');
        $this->Response->setStatus(new \Talis\Message\Status\Code422());
        $this->Response->markError();
        return $this;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->debug($this->Request->getUri() . ' CHAIN ENDS abruptly: trying to create a duplicate resource');
        \Talis\TalisMain::logger()->debug('RESPONSE: ');
        \Talis\TalisMain::logger()->debug($this->Response);
        $emitter->emit($this->Response);
    }
}

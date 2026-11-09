<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Default success link: a 200 with the message "GREAT SUCCESS!".
 *
 * @author Itay Moav
 * @Date  2017-05-22
 */
class DoneSuccessful extends aChainLink implements \Talis\commons\iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setMessage('GREAT SUCCESS!');
        $this->Response->setStatus(new \Talis\Message\Status\Code200());
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

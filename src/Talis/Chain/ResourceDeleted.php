<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Reports a deleted resource (204).
 *
 * @author Itay Moav
 * @Date  2021-04-05
 */
class ResourceDeleted extends aChainLink implements \Talis\commons\iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setMessage('Resource deleted');
        $this->Response->setStatus(new \Talis\Message\Status\Code204());
        $this->Response->markResponse();
        return $this;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->debug($this->Request->getUri() . ' RESOURCE DELETED!');
        \Talis\TalisMain::logger()->debug('RESPONSE: ');
        \Talis\TalisMain::logger()->debug($this->Response);
        $emitter->emit($this->Response);
    }
}

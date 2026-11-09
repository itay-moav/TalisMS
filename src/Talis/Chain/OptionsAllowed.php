<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Answers an OPTIONS request with a 204 and an Allow header.
 * Override allowed() to list the methods the resource takes.
 *
 * @author Itay Moav
 * @Date  2019-06-06
 */
class OptionsAllowed extends aChainLink implements \Talis\commons\iRenderable
{
    /**
     * The Allow header value, for example "GET, POST, OPTIONS".
     */
    protected function allowed(): string
    {
        return 'OPTIONS';
    }

    #[\Override]
    final public function process(): aChainLink
    {
        $this->Response->setHeader('Allow: ' . $this->allowed());
        return $this;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->debug($this->Request->getUri() . ' FINISHED CHAIN WITH optional SUCCESS');
        $this->Response->setMessage('Allowed Options');
        $this->Response->setStatus(new \Talis\Message\Status\Code204());
        $this->Response->markResponse();
        $emitter->emit($this->Response);
    }
}

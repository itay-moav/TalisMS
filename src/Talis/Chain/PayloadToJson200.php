<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Ends the chain by sending the payload alone as JSON, with a 200.
 * It writes the output itself; the emitter is not used.
 *
 * Contract: render() encodes first and sends after. If encoding throws
 * (\JsonException), no header and no output has been sent, so the door
 * can still answer with a 500.
 *
 * @author Itay Moav
 * @date 2020-04-30
 */
class PayloadToJson200 extends aChainLink implements \Talis\commons\iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setStatus(new \Talis\Message\Status\Code200());
        $this->Response->markResponse();
        return $this;
    }

    /**
     * @throws \JsonException if the payload can't be encoded
     */
    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        $json = json_encode($this->Response->getPayload(), JSON_THROW_ON_ERROR);

        \Talis\TalisMain::logger()->debug('JSON RESPONSE');
        \Talis\TalisMain::logger()->debug($json);

        $this->sendHeader('HTTP/1.1 200 Ok');
        $this->sendHeader('Content-Type: application/json');
        foreach ($this->Response->getHeaders() as $header) {
            $this->sendHeader($header);
        }
        echo $json;
    }

    /**
     * Seam around header(), so tests can capture headers.
     */
    protected function sendHeader(string $header): void
    {
        header($header);
    }
}

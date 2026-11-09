<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Ends the chain by sending the payload alone as a string, with a 200.
 * Scalars and Stringable objects are cast to string; anything else is
 * sent as JSON. It writes the output itself; the emitter is not used.
 *
 * Contract: render() encodes first and sends after. If encoding throws
 * (\JsonException), no header and no output has been sent, so the door
 * can still answer with a 500.
 *
 * @author Itay Moav
 * @date 2020-04-30
 */
class PayloadToString200 extends aChainLink implements \Talis\commons\iRenderable
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setStatus(new \Talis\Message\Status\Code200());
        $this->Response->markResponse();
        return $this;
    }

    /**
     * @throws \JsonException if a non-scalar payload can't be encoded
     */
    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        $payload = $this->Response->getPayload();
        $text = is_scalar($payload) || $payload instanceof \Stringable
            ? (string) $payload
            : json_encode($payload, JSON_THROW_ON_ERROR);

        \Talis\TalisMain::logger()->debug('PAYLOAD RESPONSE STRINGIFIED');
        \Talis\TalisMain::logger()->debug($text);

        $this->sendHeader('HTTP/1.1 200 Ok');
        foreach ($this->Response->getHeaders() as $header) {
            $this->sendHeader($header);
        }
        echo $text;
    }

    /**
     * Seam around header(), so tests can capture headers.
     */
    protected function sendHeader(string $header): void
    {
        header($header);
    }
}

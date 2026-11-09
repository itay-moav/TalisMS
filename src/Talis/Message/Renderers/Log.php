<?php

declare(strict_types=1);

namespace Talis\Message\Renderers;

/**
 * Sends a Response to the logger only. Used to record the end of a chain
 * that never reaches a real emitter.
 */
class Log implements \Talis\commons\iEmitter
{
    #[\Override]
    public function emit(\Talis\Message\Response $message): void
    {
        $body = json_encode($message->getBody(), JSON_THROW_ON_ERROR);

        if ($message->getResponseType() !== \Talis\Message\ResponseType::Response) {
            \Talis\TalisMain::logger()->fatal("CHAIN BROKE {$body}", true);
        } else {
            \Talis\TalisMain::logger()->debug('END OF PROCESS');
            \Talis\TalisMain::logger()->debug($body);
        }
    }
}

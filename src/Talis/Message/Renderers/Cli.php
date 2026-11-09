<?php

declare(strict_types=1);

namespace Talis\Message\Renderers;

/**
 * Sends a Response as plain text for a CLI run: pretty JSON then a FIN line.
 */
class Cli implements \Talis\commons\iEmitter
{
    #[\Override]
    public function emit(\Talis\Message\Response $message): void
    {
        $stat = $message->getStatus()->getCode();

        // Encode first: if this throws, nothing has been echoed.
        $body = json_encode($message->getBody(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        echo $body;
        echo "\nFIN with [{$stat}]\n";
    }
}

<?php

declare(strict_types=1);

namespace Talis\Doors;

use Talis\commons\iEmitter;
use Talis\Message\Response;
use Talis\TalisMain;

/**
 * Door for calling Talis from inside other PHP code. The caller builds
 * the body itself, so there is no parsing. Pass a quiet emitter and read
 * the returned Response if nothing should be printed.
 */
class CliEmbedded extends aDoor
{
    public function __construct(iEmitter $emitter = new \Talis\Message\Renderers\Cli())
    {
        parent::__construct($emitter);
    }

    public function gogogo(string $url, \stdClass $body): Response
    {
        TalisMain::logger()->debug('Embedded CLI body');
        TalisMain::logger()->debug($body);

        return $this->dispatch($this->uriParts($url), $body, $url);
    }
}

<?php

declare(strict_types=1);

namespace Talis\Doors;

use Talis\commons\iEmitter;
use Talis\Message\Response;
use Talis\TalisMain;

/**
 * Door for queued messages, such as spool files read by SpoolWorker.
 * Takes the raw JSON of one message:
 *   {"url": "/event/repeat/create", "params": {...}}
 * The url picks the route. The rest of the object, without url, is the
 * Request body.
 */
class Daemon extends aDoor
{
    public function __construct(iEmitter $emitter = new \Talis\Message\Renderers\Log())
    {
        parent::__construct($emitter);
    }

    public function gogogo(string $rawJson): Response
    {
        TalisMain::logger()->debug('Spool message received');
        TalisMain::logger()->debug($rawJson);

        try {
            $message = $this->decodeJsonObject($rawJson);
        } catch (\JsonException | \UnexpectedValueException $e) {
            return $this->badInput('Invalid JSON message: ' . $e->getMessage());
        }

        if ($message === null) {
            return $this->badInput('Message is empty');
        }

        $url = $message->url ?? null;
        if (!is_string($url) || $url === '') {
            return $this->badInput('Message has no url');
        }

        $body = clone $message;
        unset($body->url);
        return $this->dispatch($this->uriParts($url), $body, $url);
    }
}

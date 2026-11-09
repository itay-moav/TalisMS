<?php

declare(strict_types=1);

namespace Talis\Doors;

use Talis\commons\iEmitter;
use Talis\Message\Response;
use Talis\Message\ResponseType;
use Talis\TalisMain;

/**
 * Door for command line calls: a URL and an optional JSON body,
 * which may be base64 encoded.
 *
 * Examples:
 *   lord_commander /test/ping/read '{"params":{"n":1}}'
 *   lord_commander /test/ping/read <base64 of the JSON> 1
 *
 * gogogo() returns an exit code and never ends the process itself.
 */
class Cli extends aDoor
{
    /**
     * The final Response is a normal (non error) response.
     */
    public const int EXIT_OK = 0;

    /**
     * Error or dependency response, bad input or an exception.
     */
    public const int EXIT_ERROR = 1;

    public function __construct(iEmitter $emitter = new \Talis\Message\Renderers\Cli())
    {
        parent::__construct($emitter);
    }

    public function gogogo(string $url, ?string $rawBody = null, bool $isBase64 = false): int
    {
        TalisMain::logger()->debug("Cli url [{$url}]");
        TalisMain::logger()->debug($rawBody);

        try {
            $body = $this->decodeBody($rawBody, $isBase64);
        } catch (\JsonException | \UnexpectedValueException $e) {
            return $this->exitCode($this->badInput('Invalid request body: ' . $e->getMessage()));
        }
        return $this->exitCode($this->dispatch($this->uriParts($url), $body, $url));
    }

    /**
     * @throws \JsonException|\UnexpectedValueException on bad base64 or a body that is not a JSON object
     */
    protected function decodeBody(?string $rawBody, bool $isBase64): ?\stdClass
    {
        if ($rawBody === null || $rawBody === '') {
            return null;
        }

        if ($isBase64) {
            $rawBody = base64_decode($rawBody, true);
            if ($rawBody === false) {
                throw new \UnexpectedValueException('Body is not valid base64');
            }
        }
        return $this->decodeJsonObject($rawBody);
    }

    protected function exitCode(Response $Response): int
    {
        return $Response->getResponseType() === ResponseType::Response ? self::EXIT_OK : self::EXIT_ERROR;
    }
}

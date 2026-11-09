<?php

declare(strict_types=1);

namespace Talis\Doors;

use Talis\commons\iEmitter;
use Talis\Message\aStatus;
use Talis\Message\Response;
use Talis\Message\Status\Code400;
use Talis\Message\Status\Code500;
use Talis\TalisMain;

/**
 * Shared base for every door. A door turns its input (HTTP request, CLI
 * args, spool file) into request parts, a body and a URI, runs the chain
 * and sends the result through its emitter.
 *
 * Contract: a render() that throws must not have sent headers or output.
 * dispatch() then emits a 500 in its place.
 */
abstract class aDoor
{
    public function __construct(protected readonly iEmitter $emitter)
    {
    }

    /**
     * Runs the chain and renders the last link. Never ends the process.
     * Returns the Response that was emitted, or the chain Response when
     * the last link wrote its own output.
     *
     * @param list<string> $requestParts
     */
    protected function dispatch(array $requestParts, ?\stdClass $body, string $fullUri): Response
    {
        try {
            $FinalLink = (new TalisMain())->begin($requestParts, $body, $fullUri)->nextLinkInChain();
            // Keeps the last emitted Response, so the door returns what was really sent.
            $catcher = new class ($this->emitter) implements iEmitter {
                public ?Response $last = null;

                public function __construct(private readonly iEmitter $emitter)
                {
                }

                #[\Override]
                public function emit(Response $message): void
                {
                    $this->emitter->emit($message);
                    $this->last = $message;
                }
            };
            $FinalLink->render($catcher);
            return $catcher->last ?? $FinalLink->getResponse();
        } catch (\Talis\Exception\ParamNotFound | \Talis\Exception\InvalidParam $e) {
            return $this->badInput($e->getMessage());
        } catch (\Throwable $e) {
            TalisMain::logger()->fatal($e, true);
            $Response = $this->exceptionResponse($e);
            $this->emitter->emit($Response);
            return $Response;
        }
    }

    /**
     * Builds (does not emit) the 500 Response for an exception.
     */
    protected function exceptionResponse(\Throwable $e): Response
    {
        return $this->errorResponse(new Code500(), (string) $e);
    }

    /**
     * Answers bad input with a 400 without running the chain.
     */
    protected function badInput(string $reason): Response
    {
        TalisMain::logger()->warning($reason, false);
        $Response = $this->errorResponse(new Code400(), $reason);
        $this->emitter->emit($Response);
        return $Response;
    }

    /**
     * The message is scrubbed to valid UTF-8 so the emitter can JSON encode it.
     */
    protected function errorResponse(aStatus $status, string $message): Response
    {
        $Response = new Response();
        $Response->markError();
        $Response->setStatus($status);
        $Response->setMessage(mb_scrub($message, 'UTF-8'));
        return $Response;
    }

    /**
     * Splits a URI path on '/', after dropping the query string and a trailing slash.
     *
     * @return list<string>
     */
    protected function uriParts(string $uri): array
    {
        return explode('/', rtrim(explode('?', $uri, 2)[0], '/'));
    }

    /**
     * Decodes a body that must be a JSON object. Empty input and a JSON null give null.
     *
     * @throws \JsonException on bad JSON
     * @throws \UnexpectedValueException when the JSON is not an object
     */
    protected function decodeJsonObject(string $raw): ?\stdClass
    {
        if (trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
        if ($decoded === null || $decoded instanceof \stdClass) {
            return $decoded;
        }
        throw new \UnexpectedValueException('Request body must be a JSON object');
    }
}

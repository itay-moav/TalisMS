<?php

declare(strict_types=1);

namespace Talis\Doors;

use Talis\commons\iEmitter;
use Talis\Message\Response;
use Talis\Message\Status\Code500;
use Talis\TalisMain;

/**
 * Door for web requests. The URI path after the root URI must hold the
 * three route parts, for example /event/repeat/read.
 *
 * Form posts (urlencoded or multipart) are not JSON decoded: the body is
 * null and the data stays in $_POST. Any other non-empty body must be a
 * JSON object, or the request gets a 400.
 */
class HTTP extends aDoor
{
    /**
     * The request URI after the root URI is removed, query string included.
     */
    protected string $fullUri = '';

    /**
     * The sub folder the door is served from, e.g. '/talisroot' for
     * example.com/talisroot. '' when served from the domain root.
     */
    protected string $rootUri = '';

    /**
     * @param bool $showExceptions put the exception text and stack in 500 responses
     */
    public function __construct(iEmitter $emitter = new \Talis\Message\Renderers\HTTP(), protected readonly bool $showExceptions = false)
    {
        parent::__construct($emitter);
    }

    public function gogogo(string $rootUri): Response
    {
        TalisMain::logger()->debug('Request log starts here');
        $this->rootUri = $rootUri;
        TalisMain::logger()->debug("Root URI [{$rootUri}]");

        $requestParts = $this->getUriFromServer();
        try {
            $body = $this->getRequestBody();
        } catch (\JsonException | \UnexpectedValueException $e) {
            return $this->badInput('Invalid JSON body: ' . $e->getMessage());
        }
        return $this->dispatch($requestParts, $body, $this->fullUri);
    }

    /**
     * Strips the root URI and splits the path. The root is stripped only when
     * it is a whole path part: '/api' strips '/api/x' but not '/apiary/x'.
     *
     * @return list<string>
     */
    protected function getUriFromServer(): array
    {
        $uri = $this->serverValue('REQUEST_URI', '/');
        if ($this->rootUri !== '' && $this->startsWithRoot($uri)) {
            $uri = substr($uri, strlen($this->rootUri));
        }
        $this->fullUri = $uri;
        return $this->uriParts($uri);
    }

    private function startsWithRoot(string $uri): bool
    {
        $root = $this->rootUri;
        return $uri === $root || str_starts_with($uri, $root . '/') || str_starts_with($uri, $root . '?');
    }

    /**
     * @throws \JsonException|\UnexpectedValueException on a body that is not a JSON object
     */
    protected function getRequestBody(): ?\stdClass
    {
        if ($this->isFormContentType()) {
            return null;
        }

        $raw = $this->readInput();
        TalisMain::logger()->debug('Raw input from client');
        TalisMain::logger()->debug($raw);
        return $this->decodeJsonObject($raw);
    }

    /**
     * Seam for tests: the raw request body.
     */
    protected function readInput(): string
    {
        return (string) file_get_contents('php://input');
    }

    protected function isFormContentType(): bool
    {
        $contentType = strtolower($this->serverValue('CONTENT_TYPE', ''));
        return str_starts_with($contentType, 'application/x-www-form-urlencoded')
            || str_starts_with($contentType, 'multipart/form-data');
    }

    /**
     * $_SERVER[$key] when it is a string, else $default.
     */
    protected function serverValue(string $key, string $default): string
    {
        $value = $_SERVER[$key] ?? null;
        return is_string($value) ? $value : $default;
    }

    /**
     * Hides the exception unless showExceptions is on.
     */
    #[\Override]
    protected function exceptionResponse(\Throwable $e): Response
    {
        if (!$this->showExceptions) {
            return $this->errorResponse(new Code500(), 'An error has occurred.');
        }

        $Response = parent::exceptionResponse($e);
        $stack = array_map(
            static fn (string $line): string => mb_scrub($line, 'UTF-8'),
            explode("\n", $e->getTraceAsString()),
        );
        $Response->setPayload((object) ['TalisErrorStack' => $stack]);
        return $Response;
    }
}

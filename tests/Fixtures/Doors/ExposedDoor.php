<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Doors\aDoor;
use Talis\Message\aStatus;
use Talis\Message\Response;

/**
 * Concrete aDoor that makes the protected helpers public for tests.
 */
final class ExposedDoor extends aDoor
{
    /**
     * @param list<string> $requestParts
     */
    public function callDispatch(array $requestParts, ?\stdClass $body, string $fullUri): Response
    {
        return $this->dispatch($requestParts, $body, $fullUri);
    }

    public function callExceptionResponse(\Throwable $e): Response
    {
        return $this->exceptionResponse($e);
    }

    public function callBadInput(string $reason): Response
    {
        return $this->badInput($reason);
    }

    public function callErrorResponse(aStatus $status, string $message): Response
    {
        return $this->errorResponse($status, $message);
    }

    /**
     * @return list<string>
     */
    public function callUriParts(string $uri): array
    {
        return $this->uriParts($uri);
    }

    public function callDecodeJsonObject(string $raw): ?\stdClass
    {
        return $this->decodeJsonObject($raw);
    }
}

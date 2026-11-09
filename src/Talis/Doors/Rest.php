<?php

declare(strict_types=1);

namespace Talis\Doors;

/**
 * Door for REST calls. The URI holds two route parts, for example
 * /event/repeat, and the HTTP method gives the third (POST = create,
 * GET = read, PUT = update, DELETE = delete, ...).
 */
class Rest extends HTTP
{
    /**
     * @return list<string>
     */
    #[\Override]
    protected function getUriFromServer(): array
    {
        $requestParts = parent::getUriFromServer();
        array_splice($requestParts, 3, 0, [$this->methodToAction($this->serverValue('REQUEST_METHOD', 'GET'))]);
        return $requestParts;
    }

    protected function methodToAction(string $httpMethod): string
    {
        return match (strtoupper($httpMethod)) {
            'POST' => 'create',
            'PUT' => 'update',
            'PATCH' => 'partialupdate',
            'DELETE' => 'delete',
            'HEAD' => 'head',
            'OPTIONS' => 'options',
            default => 'read',
        };
    }
}

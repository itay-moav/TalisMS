<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Router;

use Talis\Exception\BadUri;

/**
 * Router whose constructor rejects the URI.
 */
final class BadUriInConstructorRouter extends FixedRouterBase
{
    /**
     * @param list<string> $requestParts
     * @param array<array-key, mixed>|null $getParams
     */
    public function __construct(array $requestParts, ?array $getParams = null)
    {
        parent::__construct($requestParts, $getParams);
        throw new BadUri('rejected in constructor');
    }
}

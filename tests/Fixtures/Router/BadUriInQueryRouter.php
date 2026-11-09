<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Router;

use Talis\Exception\BadUri;

/**
 * Router whose generateQuery() rejects the URI.
 */
final class BadUriInQueryRouter extends FixedRouterBase
{
    #[\Override]
    public function generateQuery(): array
    {
        throw new BadUri('rejected in generateQuery');
    }
}

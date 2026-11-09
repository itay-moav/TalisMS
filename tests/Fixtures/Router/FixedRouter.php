<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Router;

use Talis\Chain\aChainLink;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Router\aRouter;

/**
 * Router that ignores the URI and always starts the chain with FixedHead.
 */
final class FixedRouter extends aRouter
{
    #[\Override]
    public function generateRoute(): void
    {
    }

    #[\Override]
    public function generateQuery(): array
    {
        return $this->getParams;
    }

    #[\Override]
    public function getChainHead(Request $Request, Response $Response): aChainLink
    {
        return new FixedHead($Request, $Response);
    }
}

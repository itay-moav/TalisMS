<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Router;

use Talis\Chain\aChainLink;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Router\aRouter;

/**
 * Base for test routers: no route work, chain starts with FixedHead.
 */
abstract class FixedRouterBase extends aRouter
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

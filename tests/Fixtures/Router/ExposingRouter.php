<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Router;

use Talis\Chain\aChainLink;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Router\aRouter;

/**
 * Router that exposes what aRouter stored, for the aRouter tests.
 */
final class ExposingRouter extends aRouter
{
    /**
     * @return list<string>
     */
    public function parts(): array
    {
        return $this->requestParts;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getParams(): array
    {
        return $this->getParams;
    }

    #[\Override]
    public function generateRoute(): void
    {
    }

    #[\Override]
    public function generateQuery(): array
    {
        return [];
    }

    #[\Override]
    public function getChainHead(Request $Request, Response $Response): aChainLink
    {
        throw new \LogicException('not used');
    }
}

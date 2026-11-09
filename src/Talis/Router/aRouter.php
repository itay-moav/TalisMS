<?php

declare(strict_types=1);

namespace Talis\Router;

/**
 * Turns the request parts into the chain head and the GET params.
 *
 * @author itay
 * @date 2019-07-16
 */
abstract class aRouter
{
    /**
     * Query-string params, from the constructor or $_GET.
     *
     * @var array<array-key, mixed>
     */
    protected array $getParams;

    /**
     * @param list<string> $requestParts the URI path split on '/'
     * @param array<array-key, mixed>|null $getParams null means read $_GET
     */
    public function __construct(protected array $requestParts, ?array $getParams = null)
    {
        $this->getParams = $getParams ?? $_GET;
        \Talis\TalisMain::logger()->debug('request_parts');
        \Talis\TalisMain::logger()->debug($requestParts);
    }

    /**
     * Works out which API class starts the chain.
     *
     * @throws \Talis\Exception\BadUri
     */
    abstract public function generateRoute(): void;

    /**
     * The GET params: the ones in the path plus the query string.
     *
     * @return array<array-key, mixed>
     */
    abstract public function generateQuery(): array;

    /**
     * Builds the first link of the chain, the API class from the route.
     *
     * @throws \Talis\Exception\BadUri
     */
    abstract public function getChainHead(\Talis\Message\Request $Request, \Talis\Message\Response $Response): \Talis\Chain\aChainLink;
}

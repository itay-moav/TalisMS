<?php

declare(strict_types=1);

namespace Talis;

/**
 * Main entry point after a Door has read its input.
 * Routes the request to the API class that heads the chain.
 *
 * @author Itay Moav
 * @Date  2017-05-19
 */
class TalisMain
{
    /**
     * Root folder of the app. Set it in the app bootstrap.
     */
    public static string $appPath = '';

    /**
     * Run after the chain head is built, on a good route only.
     * Apps use it to log someone in or check roles.
     */
    public static ?\Closure $registeredInitFunc = null;

    /**
     * @var class-string<\Talis\Router\aRouter>
     */
    public static string $registeredRouter = \Talis\Router\DefaultRouter::class;

    /**
     * Process state that is not part of the Request or Response.
     * A fresh one is made at the start of every begin().
     */
    public static \Talis\Context $context;

    /**
     * Null until setLogger() is called; logger() then falls back to a cached NullLogger.
     */
    private static ?\Talis\commons\iLogger $logger = null;

    /**
     * Head of the chain: the API class from the route, or ApiNotFound.
     */
    private \Talis\Chain\aChainLink $requestChainHead;

    private \Talis\Message\Request $Request;

    public static function setLogger(\Talis\commons\iLogger $logger): void
    {
        self::$logger = $logger;
    }

    public static function logger(): \Talis\commons\iLogger
    {
        return self::$logger ??= new \Talis\commons\NullLogger();
    }

    /**
     * Builds the Request and the chain head. A BadUri from the router or the
     * init func gives an ApiNotFound head, so the client gets a 404.
     *
     * @param list<string> $requestParts the URI path split on '/'
     */
    public function begin(array $requestParts, ?\stdClass $requestBody, string $fullUri): static
    {
        self::$context = new \Talis\Context();
        $Response = new \Talis\Message\Response();

        try {
            $router = new self::$registeredRouter($requestParts);
            $getParams = $router->generateQuery();
        } catch (\Talis\Exception\BadUri $e) {
            // no GET params yet, so the 404 gets a Request without them
            return $this->notFound(new \Talis\Message\Request($fullUri, [], $requestBody), $Response, $e);
        }

        self::logger()->debug('BUILDING REQUEST WITH BODY');
        self::logger()->debug($requestBody);
        $this->Request = new \Talis\Message\Request($fullUri, $getParams, $requestBody);

        try {
            $router->generateRoute();
            $this->requestChainHead = $router->getChainHead($this->Request, $Response);
            if (self::$registeredInitFunc !== null) {
                (self::$registeredInitFunc)($this->Request);
            }
        } catch (\Talis\Exception\BadUri $e) {
            return $this->notFound($this->Request, $Response, $e);
        }
        return $this;
    }

    private function notFound(\Talis\Message\Request $Request, \Talis\Message\Response $Response, \Talis\Exception\BadUri $e): static
    {
        $this->Request = $Request;
        $this->requestChainHead = new Chain\Errors\ApiNotFound($Request, $Response, [$e->getMessage()]);
        return $this;
    }

    /**
     * Runs the chain and returns its last link, which the Door renders.
     *
     * @throws \Talis\Exception\LastLinkNotRenderable
     */
    public function nextLinkInChain(): \Talis\Chain\aChainLink&\Talis\commons\iRenderable
    {
        $last = $this->requestChainHead->nextLinkInChain();
        if (!$last instanceof \Talis\commons\iRenderable) {
            throw new \Talis\Exception\LastLinkNotRenderable($last::class);
        }
        return $last;
    }
}

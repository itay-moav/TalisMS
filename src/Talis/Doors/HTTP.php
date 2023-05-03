<?php namespace Talis\Doors;

/**
 * Main entry point for the request chain
 * Translate the input into the initial request object
 * and moves it along
 *
 * Will assume 3 levels [action][subaction][type] for example event/repeat/create|update|read|delete
 *
 * Loads the right controller and action.
 * Renders the $Result of the action
 * Can handle page caching.
 * Error handling
 */
class HTTP
{

    /**
     *
     * @var string $full_uri
     */
    protected string $full_uri;
    /**
     * @var string $root_uri The relative subfolder to the domain. 
     * 
     * If your system door is accessible at example.com/talisroot then the root uri is /talisroot
     */
    protected string $root_uri;

    /**
     * Starts the chain reaction.
     * builds request/check dependencies/run main logic
     *
     * @param string $root_uri
     *            The relative subfolder to the domain. If your system door is accessible at example.com/talisroot then the root uri is /talisroot
     *            if it is just example.com, then it is ''
     */
    public function gogogo(string $root_uri):void
    {
        \ZimLogger\MainZim::$CurrentLogger->debug("\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nREQUEST LOG STARTS HERE!");
        $this->root_uri = $root_uri;
        try {
            // Corwin is the first step in the general chain. It is NOT tailored specificly for the http request.
            (new \Talis\Corwin())->begin($this->get_uri_from_server(), $this->get_request_body(), $this->full_uri)
                ->nextLinkInchain()
                ->render(new \Talis\Message\Renderers\HTTP());
        } catch (\Throwable $e) { // TODO for now, all errors are Corwin, better handling later
            \ZimLogger\MainZim::$CurrentLogger->fatal($e,true);
            $response = new \Talis\Message\Response();
            $response->markError();
            $response->setStatus(new \Talis\Message\Status\Code500());
            if(defined('SHOW_EXCEPTIONS')){
                $response->setMessage($e . '');
                $response->getPayload()->TalisErrorStack = $e->getTrace();
            } else {
                $response->setMessage('An error has occured. Chaos Snakes have been dispatched.');
            }
            
            (new \Talis\Message\Renderers\HTTP())->emit($response);
        }
    }

    /**
     * Parses the server input to generate raw uri parts
     * @return array<string>
     */
    protected function get_uri_from_server(): array
    {
        $this->full_uri = $this->root_uri ? explode($this->root_uri, $_SERVER['REQUEST_URI'])[1] : $_SERVER['REQUEST_URI'];//@phpstan-ignore-line

        // remove ? and after if exists
        $without_question = rtrim(explode('?', $this->full_uri)[0], '/');
        $request_parts = explode('/', $without_question);
        return $request_parts;
    }

    /**
     * Parses the http input stream to get the body and decode into stdClass
     *
     * @return ?\stdClass
     */
    protected function get_request_body(): ?\stdClass
    {
        $json_request_body = file_get_contents('php://input');
        \ZimLogger\MainZim::$CurrentLogger->debug('RAW INPUT FROM CLIENT');
        \ZimLogger\MainZim::$CurrentLogger->debug($json_request_body);
        return json_decode($json_request_body?:'');
    }
}

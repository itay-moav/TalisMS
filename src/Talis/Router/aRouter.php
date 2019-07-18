<?php namespace Talis\Router;

/**
 * Define the router methods
 * 
 * @author itay
 * @date 2019-07-16
 */
abstract class aRouter{
    /**
     * Original request parts broken down by the respective doors of this request
     * @var array
     */
    protected $request_parts = [];
    
    /**
     * Holds data about the initial API class, path and class name, usually
     * 
     * @var array
     */
    protected $route = [];
    
    public function __construct(array $request_parts){
        \dbgr('request_parts',$request_parts);
        $this->request_parts = $request_parts;
    }
    
    /**
     * Generates the API class name. This will be the name
     * of the class to start the chain, business wise
     *
     * ASSUMES CONVENTION OF 3 LEVELS URL [action][subaction][type]
     *
     * array [route=>the path to the class, classname=>the name of the class]
     */
    abstract public function generate_route():void;
    
    /**
     * Return would be GET params from butified urls
     * @return array
     */
    abstract public function generate_query():array;
    
    /**
     * Instantiate the first step in the chain, The API class that we got from the route.
     * Or, an error response, if API does not exist
     *
     * @throws \Talis\Exception\BadUri
     * @return \Talis\Chain\aFilteredValidatedChainLink
     */
    abstract public function get_chainhead(\Talis\Message\Request $Request, \Talis\Message\Response $Response):\Talis\Chain\aChainLink;
    
}

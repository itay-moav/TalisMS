<?php namespace Talis\Router;


class DefaultRouter extends aRouter{
    /**
     * Generates the API class name. This will be the name
     * of the class to start the chain, business wise
     *
     * ASSUMES CONVENTION OF 3 LEVELS URL [action][subaction][type]
     *
     * array [route=>the path to the class, classname=>the name of the class]
     */
    public function generate_route():void{
        if(count($this->request_parts) < 3){
            throw new \Talis\Exception\BadUri(print_r($this->request_parts,true));
        }
        
        $this->route= [
            'route'      => APP_PATH . "/api/{$this->request_parts[1]}/{$this->request_parts[2]}/{$this->request_parts[3]}.php",
            'classname'  => "\Api\\{$this->request_parts[1]}{$this->request_parts[2]}{$this->request_parts[3]}"
            ];
        \dbgn("Doing route [{$this->route['route']}]");
    }
    
    /**
     * Return would be GET params from butified urls
     * @return array
     */
    public function generate_query():array{
        $c = count($this->request_parts);
        $extra_params = [];
        for($i=4; $i<$c;$i+=2){
            $extra_params[$this->request_parts[$i]] = ($this->request_parts[$i+1]??true);
        }
        \dbgr('GET PARAMS',$extra_params);
        return $extra_params;
    }
    
    /**
     * Instantiate the first step in the chain, The API class that we got from the route.
     * Or, an error response, if API does not exist
     *
     * @throws \Talis\Exception\BadUri
     */
    public function get_chainhead(\Talis\Message\Request $Request, \Talis\Message\Response $Response): \Talis\Chain\aFilteredValidatedChainLink
    {
        \dbgn("TRYING TO INCLUDE: {$this->route['route']}");
        if (! @include_once $this->route['route']) {
            throw new \Talis\Exception\BadUri($this->route['route']);
        }
        return new $this->route['classname']($Request, $Response);
    }
    
}

<?php namespace Talis\Router;

/**
 * @author itay
 */
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
        if(count($this->request_parts) < 4){ //one root path + three parts to define the API 
            throw new \Talis\Exception\BadUri(print_r($this->request_parts,true));
        }
        
        $this->route= [
            'route'      => \Talis\TalisMain::$APP_PATH . "/api/{$this->request_parts[1]}/{$this->request_parts[2]}/{$this->request_parts[3]}.php",
            'classname'  => "\Api\\{$this->request_parts[1]}{$this->request_parts[2]}{$this->request_parts[3]}"
        ];
        \Talis\TalisMain::logger()->debug("Doing route [{$this->route['route']}]");
    }
    
    /**
     * Return would be GET params from butified urls
     * @return array<string>
     */
    public function generate_query():array{
        $c = count($this->request_parts);
        $extra_params = [];
        for($i=4; $i<$c;$i+=2){
            $extra_params[$this->request_parts[$i]] = ($this->request_parts[$i+1]??'');
        }

        if(count($_GET)>0){
            $extra_params = array_merge($extra_params,$_GET);
            \Talis\TalisMain::logger()->debug('extra with HTTP GET params');
            \Talis\TalisMain::logger()->debug($extra_params);
        }
        return $extra_params;
    }

    /**
     * Instantiate the first step in the chain, The API class that we got from the route.
     * Or, an error response, if API does not exist
     *
     * @throws \Talis\Exception\BadUri
     * {@inheritDoc}
     * @see \Talis\Router\aRouter::get_chainhead()
     */
    public function get_chainhead(\Talis\Message\Request $Request, \Talis\Message\Response $Response): \Talis\Chain\aChainLink
    {
        $full_path_route = $this->route['route'];
        \Talis\TalisMain::logger()->debug("TRYING TO INCLUDE: {$full_path_route}");
        if(file_exists($full_path_route)){
            require_once $full_path_route;
        } else {            
            throw new \Talis\Exception\BadUri($full_path_route);
        }
        return new $this->route['classname']($Request, $Response);
    }
}

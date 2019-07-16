<?php namespace Talis;

/**
 * MAIN APP ENTRY POINT!
 * 
 * Responsebility: Parses the user input to identify the API class to instantiate
 * This is the ROUTER
 * 
 * @author Itay Moav
 * @Date  2017-05-19
 */
class Corwin{
	/**
	 * I am setting this up in the specific apps using Talis. 
	 * I usually would like to use  it to login someone or check generic roles etc
	 * 
	 * @var callable a function to run on init.
	 */
	static public $registered_init_func = null;
	
	/**
	 * Context for this process, which is not part of the Response Request
	 * @var \Talis\Data\Context
	 */
	static public $Context = null;
	
	/**
	 * 
	 * @var DefaultRouter
	 */
	private $Router = null;
	
	/**
	 * @var array
	 */
	private $extra_params = [];
	
	/**
	 * Body of the request, json decoded string
	 * @var \stdClass
	 */
	private $req_body  = null;
	
	/**
	 * The head of the BL process chain, usually that would be the API head class built from the route,
	 * but on occassions that would be the error class, in case there where issues
	 * 
	 * @var \Talis\Chain\aChainLink $RequestChainHead
	 */
	private $RequestChainHead = null;

	/**
	 * Holds the request parameters (GET/POST etc)
	 * 
	 * @var \Talis\Message\Request $Request
	 */
	private $Request  = Null;
	
	/**
	 * Main entry point after the Door for a specific protocol is finished (http/rest/stopmp/async etc)
	 * 
	 * @param array $request_parts
	 * @param \stdClass $request_body
	 * @param string $full_uri
	 * @return \Talis\Corwin
	 */
	public function begin(array $request_parts,?\stdClass $request_body,string $full_uri){
		$this->req_body = $request_body;
		self::$Context = new \Talis\Data\Context;
		
		try{
		    $this->Router = new DefaultRouter($request_parts);
		    $this->Router->generate_route();
			$this->extra_params = $this->Router->generate_query();
			
			$this->build_request($full_uri);
			$this->RequestChainHead = $this->Router->get_chainhead($this->Request,new \Talis\Message\Response);

			//the dynamic init
			if(self::$registered_init_func){
				$func = self::$registered_init_func;
				$func($this->Request);
			}
			
		} catch(\Talis\Exception\BadUri $e){
		    $req = $this->Request ?: null;
			$this->RequestChainHead = new Chain\Errors\ApiNotFound($req,null,[$e->getMessage()]);
		}
		return $this;
	}
	
	/**
	 * Init the API class and call it's dependency checks
	 * 
	 * @return Chain\aChainLink
	 */
	public function nextLinkInchain():\Talis\commons\iRenderable{
		return $this->RequestChainHead->nextLinkInchain();
	}
	
	private function build_request(string $full_uri):void{
		\dbgr('BUILDING REQUEST WITH BODY',$this->req_body);
		$this->Request = new \Talis\Message\Request($full_uri,$this->extra_params,$this->req_body);
	}
}







class DefaultRouter{
    protected $request_parts = [];
    
    private   $route = [];
    
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
    public function get_chainhead(\Talis\Message\Request $Request, \Talis\Message\Response $Response): \Talis\Chain\aChainLink
    {
        \dbgn("TRYING TO INCLUDE: {$this->route['route']}");
        if (! @include_once $this->route['route']) {
            throw new \Talis\Exception\BadUri($this->route['route']);
        }
        return new $this->route['classname']($Request, $Response);
    }
}

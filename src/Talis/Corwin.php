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
	 * @var array
	 */
	private $route = [
			'route'	       => 'maps to class file',
			'classname'	   => 'object to init',
			'extra_params' => []
	];
	
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
			$this->generate_route($request_parts);
			$this->generate_query($request_parts);
			$this->build_request($full_uri);
			$this->prepareResponse();
			//the dynamic init
			if(self::$registered_init_func){
				$func = self::$registered_init_func;
				$func($this->Request);
			}
			
		} catch(\Talis\Exception\BadUri $e){
			$this->RequestChainHead = new Chain\Errors\ApiNotFound(null,null,[$e->getMessage()]);
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
		$this->Request = new \Talis\Message\Request($full_uri,$this->route['extra_params'],$this->req_body);
	}
	
	/**
	 * Instantiate the first step in the chain, The API class that we got from the route.
	 * Or, an error response, if API does not exist
	 * 
	 * @throws \Talis\Exception\BadUri
	 */
	private function prepareResponse():void{
		\dbgn("TRYING TO INCLUDE: {$this->route['route']}");
		if(!include_once $this->route['route']){
			throw new \Talis\Exception\BadUri($this->route['route']);
		}
		$this->RequestChainHead = new $this->route['classname']($this->Request,new \Talis\Message\Response);
	}
	
	/**
	 * Understands from the URL what BL object to call
	 * ASSUMES CONVENTION OF 3 LEVELS URL [action][subaction][type]
	 * @param array $request_parts
	 */
	private function generate_route(array $request_parts):void{
		if(count($request_parts) < 3){
		    throw new \Talis\Exception\BadUri(print_r($request_parts,true));
		}
		$this->route['route'] = APP_PATH . "/api/{$request_parts[1]}/{$request_parts[2]}/{$request_parts[3]}.php";
		\dbgn("Doing route [{$this->route['route']}]");
		$r = $request_parts[1].$request_parts[2].$request_parts[3];
		$this->route['classname'] = '\Api\\' . $r;
	}
	
	/**
	 * See if part of the uri is actually a query string. Accepts ONLY /field/value/ffield/value...
	 * @param array $request_parts
	 */
	private function generate_query(array $request_parts):void{
		\dbgr('request_parts',$request_parts);
		$c = count($request_parts);
		for($i=4; $i<$c;$i+=2){
			$this->route['extra_params'][$request_parts[$i]] = $request_parts[$i+1];
		}
		\dbgr('GET PARAMS',$this->route['extra_params']);
	}
}
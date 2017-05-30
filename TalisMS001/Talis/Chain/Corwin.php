<?php namespace Talis\Chain;
use Talis\Logger as L;

/**
 * Responsebility: Parses the user input to identify the API class to instantiate
 * This is the ROUTER
 * 
 * @author Itay Moav
 * @Date  2017-05-19
 */
class Corwin{
	
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
	 * @var stdClass
	 */
	private $req_body  = null;
	
	/**
	 * @var \Talis\Chain\aChainLink $Response
	 * @var \Talis\Message\Request $Request
	 */
	private $Response = null,
			$Request  = Null
	;
	
	/**
	 * Main entry point after the MAIN for a specific protocol finished (http/rest/stopmp/async etc)
	 * 
	 * @param array $request_parts
	 * @param \stdClass $request_body
	 * @param string $full_uri
	 * @return \Talis\Chain\Corwin
	 */
	public function begin(array $request_parts,?\stdClass $request_body,string $full_uri){
		$this->req_body = $request_body;
		try{
			$this->generate_route($request_parts);
			$this->generate_query($request_parts);
			$this->build_request($full_uri);
			$this->prepareResponse();
		} catch(\Talis\Exception\BadUri $e){
			$this->Response = new Errors\ApiNotFound(null,[$e->getMessage()]);
		}
		return $this;
	}
	
	/**
	 * Init the API class and call it's dependency checks
	 * 
	 * @return aChainLink
	 */
	public function process():aChainLink{
		return $this->Response->process();
	}
	
	private function build_request(string $full_uri):void{
		$this->Request = new \Talis\Message\Request($full_uri,$this->route['extra_params'],$this->req_body);
	}
	
	/**
	 * Instantiate the first step in the chain, The API class that we got from the route.
	 * Or, an error response, if API does not exist
	 * 
	 * @throws \Talis\Exception\BadUri
	 */
	private function prepareResponse():void{
		if(!@include_once $this->route['route']){
			throw new \Talis\Exception\BadUri($this->route['route']);
		}
		$this->Response = new $this->route['classname']($this->Request);
	}
	
	/**
	 * Understands from the URL what BL object to call
	 * ASSUMES CONVENTION OF 4 LEVELS URL [version][action][subaction][type]
	 * @param array $server
	 */
	private function generate_route(array $request_parts):void{
		if(count($request_parts) < 4){
			throw new \Talis\Exception\BadUri($uri);
		}
		$this->route['route'] = APP_PATH . "/api/version{$request_parts[1]}/{$request_parts[2]}/{$request_parts[3]}/{$request_parts[4]}.php";
		L\dbgn("Doing route [{$this->route['route']}]");
		$r = $request_parts[2].$request_parts[3].$request_parts[4];
		$this->route['classname'] = '\Api\\' . $r;
	}
	
	/**
	 * See if part of the uri is actually a query string. Accepts ONLY /field/value/ffield/value...
	 * @param array $request_parts
	 */
	private function generate_query(array $request_parts):void{
		$c = count($request_parts);
		for($i=5; $i<$c;$i+=2){
			$this->route['extra_params'][$request_parts[$i]] = $request_parts[$i+1];
		}
		L\dbgr('GET PARAMS',$this->route['extra_params']);
	}
}
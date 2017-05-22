<?php namespace Talis\Chain;
use Talis\Logger as L;

/**
 * Responsebility: Parses the user input to identify the API class to instantiate
 * This is the ROUTER
 * 
 * @author Itay Moav
 * @Date  2017-05-19
 */
class Corwin implements iReqRes{
	
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
	private $body  = null;
	
	/**
	 * @var iReqRes
	 */
	private $Response = null;
	
	public function __construct(array $server,string $json_request_body){
		try{
			$this->generate_route($server);
			$this->generate_body($json_request_body);
			$this->prepareResponse();
		} catch(\Talis\Exception\BadUri $e){
			$this->Response = new Errors\ApiNotFound($e->getMessage());
		}
	}
	
	/**
	 * Init the API class and call it's dependency checks
	 * 
	 * @return iReqRes
	 */
	public function process():iReqRes{
		return $this->Response->process();
	}
	
	private function prepareResponse():void{
		if(!@include_once $this->route['route']){
			throw new \Talis\Exception\BadUri($this->route['route']);
		}
		$this->Response = new $this->route['classname']($this->body);
	}
	
	/**
	 * Understands from the URL what BL object to call
	 * ASSUMES CONVENTION OF 4 LEVELS URL [version][action][subaction][type]
	 * @param array $server
	 */
	private function generate_route(array $server):void{
		$uri 		   = explode(\app_env()['paths']['root_uri'],$server['REQUEST_URI'])[1];
		$request_parts = explode('/',$uri);
		if(count($request_parts) < 4){
			throw new \Talis\Exception\BadUri($uri);
		}
		
		$this->route['route'] = APP_PATH . "/api/version{$request_parts[1]}/{$request_parts[2]}/{$request_parts[3]}/{$request_parts[4]}.php";
		L\dbgn("Doing route [{$this->route['route']}]");
		unset($request_parts[1]); //version is not part of the class name
		$r = array_reduce($request_parts,function($carry, $item){$carry .= ucfirst($item);return $carry;},'');
		$this->route['classname'] = '\Api\\' . $r;
	}
	
	private function generate_body(string $json_request_body):void{
		L\dbgn('RAW INPUT FROM CLIENT');
		L\dbgn("==============={$json_request_body}===============");
		$this->body = json_decode($json_request_body);
	}
}
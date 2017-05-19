<?php namespace Talis\Chain;
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
		require_once $this->route['route'];
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
		
		$this->route['route'] = APP_PATH . "/version{$request_parts[0]}/{$request_parts[1]}/{$request_parts[2]}/{$request_parts[3]}.php";
		\Talis\Logger\dbgn("Doing route [{$this->route['route']}]");
		unset($request_parts[0]); //version is not part of the class name
		$r = array_reduce($request_parts,function($carry, $item){$carry .= ucfirst($item);return $item;},'');
		$this->route['classname'] = "\Api\{$r}";
	}
	
	private function generate_body(string $json_request_body):void{
		dbgn('RAW INPUT FROM CLIENT');
		dbgn("==============={$json_request_body}===============");
		$this->body = json_decode($input);
	}
}
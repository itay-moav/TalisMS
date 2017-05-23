<?php namespace Talis\Main;
use Talis\Logger as L;
/**
 * Main entry point for the request chain
 * Translate the input into the initial request object
 * and moves it along
 * 
 * Will assume 4 levels [version][action][subaction][type] for example 1/event/repeat/create|update|read|delete
 * 
 * Loads the right controller and action.
 * Renders the $Result of the action
 * Can handle page caching.
 * Error handling
 *  
 */
class HTTP{
	private $full_uri = '';
	
	/**
	 * Starts the chain reaction. builds request/check dependencies/run main logic
	 */
	public function gogogo(){
		$ChainConclusion = null;
		try{
			$TopChain = new \Talis\Chain\Corwin;
			$TopChain->begin($this->get_uri_from_server(),$this->get_request_body(),$this->full_uri); //Corwin is the first step in the chain. It is tailored specificly for the http request.
			$ChainConclusion = $TopChain->process();

		}catch(Exception $E){ // TODO for now, all errors are Corwin, better handling later
			L\fatal($E);
			//TODO $response = ErrorResponse($E);
		}
		$ChainConclusion->render();
	}
	
	/**
	 * Parses the server input to generate raw uri parts
	 */
	private function get_uri_from_server():array{
		$this->full_uri = explode(\app_env()['paths']['root_uri'],$_SERVER ['REQUEST_URI'])[1];
		$request_parts  = explode('/',$this->full_uri);
		return $request_parts;
	}
	
	/**
	 * Parses the http input stream to get the body and decode into stdClass
	 * @return stdClass
	 */
	private function get_request_body():?stdClass{
		$json_request_body = file_get_contents('php://input');
		L\dbgn('RAW INPUT FROM CLIENT');
		L\dbgn("==============={$json_request_body}===============");
		return json_decode($json_request_body);
	}
}


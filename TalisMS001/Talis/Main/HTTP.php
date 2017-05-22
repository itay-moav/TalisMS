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
	/**
	 * @var Talis\Chain\Corwin
	 */
	private $Request = null;
	
	public function __construct(){
		$this->Request = new \Talis\Chain\Corwin($_SERVER,file_get_contents('php://input')); //Corwin is the first step in the chain. It is tailored specificly for the http request.
	}
	
	/**
	 * Starts the chain reaction. builds request/check dependencies/run main logic
	 */
	public function gogogo(){
		try{
			$response = $this->Request->process();

		}catch(Exception $E){ // TODO for now, all errors are Corwin, better handling later
			L\fatal($E);
		}
		$response->render();
	}
}


<?php
/**
 * Main entry point, ala front controller.
 *
 * Loads the right controller and action.
 * Renders the $Result of the action
 * Can handle page caching.
 * Error handling
 * Will assume 4 levels [protocol][version][subprotocol][[type] - 
 */
class Talis{
	/**
	 * @var Request
	 */
	private $Request = null;
	
	public function __construct(){
		$this->Request = self::route_get_request_obj($_SERVER);
	}
	
	public function gogogo(){
		try{
			$Controller = $this->Request->getController(); 		// send the controller the Request
			$action = $this->Request->action;					// run the action
			$Controller->$action();
			$response = $Controller->getResponse();				// get the response.
			
		}catch(Exception_Auth_NotLoggedin $E){
			info($E);
			$response = new Response_Json($this->Request);
			$response->setData((new Corwin_Lib_ErrorAction($this->Request,$E))->consume());
		}catch(Exception $E){ // TODO for now, all errors are Corwin, better handling later
		    fatal($E);
		    $response = new Response_Json($this->Request);
		    $response->setData((new Corwin_Lib_ErrorAction($this->Request,$E))->consume());
		}
		$response->render();
	}
	
	/**
	 * Main logic for router, KISS it
	 * Modify this function to get a different router.
	 * Router is translation of url to protocol[corwin|paran]/version[rXdY]/subprotocol[stateless|statefull]/type[get|post]
	 * Paran is more free form, and used to accomodate third parties protocols. 
	 */
	static private function route_get_request_obj($server){
	    $request_parts = Corwin_Lib_Request::get_request_parts($server);
	    $Request       = new Corwin_Lib_Request($_POST);
		$Request->module        = $request_parts[0];
		$Request->sub_module    = $request_parts[1];
		$Request->controller	= $request_parts[2];
		if( (!isset($request_parts[3]) || !$request_parts[3]) && $Request->module == 'random'){
		    $Request->action = 'index';
		}else{
		    $Request->action		= $request_parts[3];
		}	
		
		dbgn('SUBDOMAIN: ' . SUBDOMAIN);
		dbgn(' Protocol: ' . $Request->module . ' -- version ' . $Request->sub_module . '-- C: ' . $Request->controller . '-- Method: ' . $Request->action);
		
		return $Request;
	}
}


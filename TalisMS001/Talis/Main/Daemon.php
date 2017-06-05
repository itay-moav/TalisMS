<?php namespace Talis\Main;
use function \Talis\Logger\dbgn,
             \Talis\Logger\fatal,
			 \Talis\Logger\dbgr;

/**
 * SERVES ActiveMQ originating messages
 * 
 * Main entry point for the request chain
 * Translate the input into the initial request object
 * and moves it along
 * 
 * Will assume a string (base64 encoded):
 * {"url":"[version][action][subaction][type]",      #for example 1/event/repeat/create|update|read|delete
 *  "params": {}
 * }     
 * 
 * 
 * Loads the right controller and action.
 * Renders the $Result of the action
 * Can handle page caching.
 * Error handling
 *  
 */
class Daemon{
	
	/**
	 * Starts the chain reaction. builds request/check dependencies/run main logic
	 */
	public function gogogo(string $raw_request){
		
		try{
			//decode
			$decoded_request = json_decode(base64_decode($raw_request));
			dbgr('RECEIVED',$decoded_request);
			
			//Corwin is the first step in the general chain. It is NOT tailored specificly for the http request.
			(new \Talis\Chain\Corwin)->begin($this->get_uri($decoded_request->url),
											 $decoded_request->params,
											 $decoded_request->url)
			                         ->process()
					                 ->render(new \Talis\Message\Renderers\HTTP)
			;

		}catch(Exception $E){ // TODO for now, all errors are Corwin, better handling later
			fatal($E);
			$response = new \Talis\Message\Response;
			$response->setBody(\Talis\commons\array_to_object(['type'=>'error','message'=>$e.'']));
			$response->setStatus(new \Talis\Message\Status\Code500);
			(new \Talis\Message\Renderers\HTTP)->emit($respone);
		}
	}
	
	/**
	 * Parses the server input to generate raw uri parts
	 */
	private function get_uri(string $uri):array{
		return explode('/',$uri);
	}
}


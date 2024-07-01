<?php namespace Talis\Doors;

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
	public function gogogo(string $raw_request):void{
		
		try{
			//decode
			$decoded_request = json_decode(base64_decode($raw_request));
			\Talis\Corwin::logger()->debug('MESSAGE RECEIVED');
			\Talis\Corwin::logger()->debug($decoded_request);
			
			//Corwin is the first step in the general chain. It is NOT tailored specificly for the http request.
			$request_parts = $this->get_uri($decoded_request->url);
		    (new \Talis\Corwin)->begin($request_parts,$decoded_request->params,$decoded_request->url)
    		    ->nextLinkInchain()
    		    ->render(new \Talis\Message\Renderers\HTTP);

		}catch(\Throwable $e){ // TODO for now, all errors are Corwin, better handling later
		    \Talis\Corwin::logger()->fatal($e,true);
			$response = new \Talis\Message\Response;
			$response->markError();
			$response->setStatus(new \Talis\Message\Status\Code500);
			$response->setMessage($e.'');
			(new \Talis\Message\Renderers\HTTP)->emit($response);
		}
	}
	
	/**
	 * Parses the server input to generate raw uri parts
	 * @return array<string>
	 */
	private function get_uri(string $uri):array{
		return explode('/',$uri);
	}
}


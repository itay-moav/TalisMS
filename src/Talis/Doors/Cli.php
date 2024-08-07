<?php namespace Talis\Doors;

/**
 * SERVES cli originating messages
 * 
 * Main entry point for the request chain
 * Translate the input into the initial request object
 * and moves it along
 * 
 * Will assume a two or three parameters sent to the cli door
 * url json_decoded_stdclass 
 * OR
 * url base64_encoded(json_string_encoded(stdclass)) is_base64_encoded
 * 
 * examples:
 * 
 * path/to/lord_commander /test/ping/read "{n:1,l:2}";
 * OR
 * path/to/lord_commander /test/ping/read "{n:1,l:2} yes";
 * 
 * Loads the right controller and action.
 * Renders the $Result of the action
 * Can handle page caching.
 * Error handling
 *  
 */
class Cli{

    /**
     * Starts the chain reaction. builds request/check dependencies/run main logic
     * @param string $url
     * @param ?string $raw_request_body
     * @param bool $is_base64_encoded
     */
	public function gogogo(string $url,?string $raw_request_body,bool $is_base64_encoded=false):void{
	    $raw_request_body = $raw_request_body?:'';
		try{
		    \Talis\TalisMain::logger()->debug('$raw_request_body');
		    \Talis\TalisMain::logger()->debug($raw_request_body);
		    
		    //decode
		    if($is_base64_encoded && $raw_request_body){
		        $raw_request_body = base64_decode($raw_request_body);
		        \Talis\TalisMain::logger()->debug('base64 decoded request_body');
		        \Talis\TalisMain::logger()->debug($raw_request_body);
		    }
		    
		    $decoded_request_body = json_decode($raw_request_body?:'');
		    \Talis\TalisMain::logger()->debug('$decoded_request_body');
		    \Talis\TalisMain::logger()->debug($decoded_request_body);
		    
		    //TalisMain is the first step in the general chain. It is NOT tailored specificly for the http request.
		    $request_parts = $this->get_uri($url);
		    (new \Talis\TalisMain)->begin($request_parts,
		        $decoded_request_body,
		        $url)
		        ->nextLinkInchain()
		        ->render(new \Talis\Message\Renderers\Cli);

		}catch(\Throwable $e){ // TODO for now, all errors are TalisMain, better handling later
		    \Talis\TalisMain::logger()->fatal($e,true);
			$response = new \Talis\Message\Response;
			$response->markError();
			$response->setStatus(new \Talis\Message\Status\Code500);
			$response->setMessage($e.'');
			(new \Talis\Message\Renderers\Cli)->emit($response);
			exit(-1);
		}
	}

	/**
	 * Parses the server input to generate raw uri parts
	 * @param string $uri
	 * @return array<string>
	 */
	private function get_uri(string $uri):array{
		return explode('/',$uri);
	}
}


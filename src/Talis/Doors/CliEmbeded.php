<?php namespace Talis\Doors;

/**
 * Embeded CLI calls in code.
 * Saves on parsing
 * 
 * Not necessarily the Main entry point for the request chain
 * Translate the input into the initial request object
 * and moves it along
 * This is embeded in other codes
 * 
 * Will assume a two parameters sent to the cli door
 * url and stdclass. The stdClass is manufacured in the code that embeds this
 */
class CliEmbeded{

    /**
     * Starts the chain reaction. builds request/check dependencies/run main logic
     * @param string $url
     * @param \stdClass $body
     */
	public function gogogo(string $url,\stdClass $body):void{
		try{
			\Talis\TalisMain::logger()->debug('EMBEDED CLI BODY');
			\Talis\TalisMain::logger()->debug($body);

			//TalisMain is the first step in the general chain. It is NOT tailored specificly for the http request.
			$request_parts = $this->get_uri($url);
			(new \Talis\TalisMain)->begin($request_parts,$body,$url)
							   ->nextLinkInchain()
					           ->render(new \Talis\Message\Renderers\Cli)
			;

		}catch(\Throwable $e){ // TODO for now, all errors are TalisMain, better handling later
		    \Talis\TalisMain::logger()->fatal($e,true);
			$response = new \Talis\Message\Response;
			$response->markError();
			$response->setStatus(new \Talis\Message\Status\Code500);
			$response->setMessage($e.'');
			(new \Talis\Message\Renderers\Cli)->emit($response);
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


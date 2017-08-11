<?php namespace Talis\Message;
class Request extends aMessage{
	private 	$full_uri   = '',
				$get_params = []
	;
	
	/**
	 * 
	 */
	public function __construct(string $full_uri,array $get_params,?\stdClass $body){
		$this->body       = $body;
		$this->full_uri   = $full_uri;
		$this->get_params = $get_params;
	}
	
	/**
	 * Full URI of this request (API+get params
	 */
	public function getUri():string{
		return $this->full_uri;		
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getAllGetParams():array{
		return $this->get_params;
	}
	
	/**
	 * 
	 * @param string $key
	 * @return string
	 */
	public function get_param(string $key):string{
		return $this->get_params[$key];
	}
	
	/**
	 * @return \stdClass
	 */
	public function getBodyParams(){
		return $this->getBody()->params;
	}
}
<?php namespace Talis\Message;
class Request{
	private 	$body 	    = null,
				$full_uri   = '',
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
	 * The json decoded body or null
	 * 
	 * @return stdClass|NULL
	 */
	public function getBody():?\stdClass{
		return $this->body;
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
}
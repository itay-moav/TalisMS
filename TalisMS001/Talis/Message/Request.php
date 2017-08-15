<?php namespace Talis\Message;
class Request extends aMessage{
	private 	$full_uri   = '',
				$get_params = []
	;
	
	/**
	 * 
	 */
	public function __construct(string $full_uri,array $get_params,?\stdClass $body){
		$this->body       = $body?:new \stdClass;
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
		if(isset($this->getBody()->params)){
			return $this->getBody()->params;
		} else {
			$this->body->params = new \stdClass;
		}
	}
	
	/**
	 * @return mixed
	 */
	public function getBodyParam($k,$default=null){
		return $this->getBody()->params->$k ?? $default;
	}
	
	/**
	 * Add values/keys to the parameter section of the body->params
	 * @param string $k
	 * @param mixed $v
	 */
	public function addToBodyParams(string $k,$v){
		return $this->getBodyParams()->$k = $v;		
	}
	
	
}
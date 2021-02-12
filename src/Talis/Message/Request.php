<?php namespace Talis\Message;
class Request{
    /**
     * @var array<string>
     */
    private array $headers = [];
    
    /**
     * @var \stdClass
     */
    private \stdClass $body;
    
    /**
     * @var string
     */
    private string $full_uri;
    
    /**
     * @var array<string>
     */
    private array $get_params = [];
	
    /**
     * @param string $full_uri
     * @param array<string> $get_params
     * @param ?\stdClass $body
     */
	public function __construct(string $full_uri,array $get_params,?\stdClass $body=null){
		$this->body       = $body?:new \stdClass;
		$this->full_uri   = $full_uri;
		$this->get_params = $get_params;
	}
	
	/**
	 * @return array<string>
	 */
	public function getHeaders():array{
	    return $this->headers;
	}
	
	/**
	 * @param string $header
	 * @return Request
	 */
	public function setHeader(string $header):Request{
	    $this->headers[] = $header;
	    return $this;
	}
	
	/**
	 * The json decoded body or stdClass
	 *
	 * @return \stdClass
	 */
	public function getBody():\stdClass{
	    return $this->body??$this->setBody(new \stdClass);
	}

	/**
	 * @param \stdClass $body
	 * @return \stdClass
	 */
	public function setBody(\stdClass $body):\stdClass{
	    return $this->body = $body;
	}

	/**
	 * Full URI of this request (API+get params)
	 * @return string
	 */
	public function getUri():string{
		return $this->full_uri;		
	}
	
	/**
	 * 
	 * @return array<string>
	 */
	public function getAllGetParams():array{
		return $this->get_params;
	}
	

	/**
	 * the url/param/value or a default 
	 * 
	 * @param string $key
	 * @param ?string $default
	 * @return ?string
	 * @deprecated
	 */
	public function get_param(string $key,?string $default=null):?string{
	    return $this->get_params[$key] ?? $default;
	}
	
	/**
	 * The url/param/value or NULL
	 * 
	 * @param string $key
	 * @return string|NULL
	 */
	public function get_param_null(string $key):?string{
	    return $this->get_params[$key] ?? null;
	}
	
	/**
	 * The url/param/value or a default 
	 * 
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function get_param_default(string $key,string $default):string{
	    return $this->get_params[$key] ?? $default;
	}
	
	/**
	 * the url/param/value
	 * This can be called if you are sure the param exists
	 * 
	 * @param string $key
	 * @return string
	 */
	public function get_param_exists(string $key):string{
	    return $this->get_params[$key];
	}
	
	/**
	 * The url/param/value
	 * Failes if no param found
	 * @param string $key
	 * @throws \Talis\Exception\ParamNotFound
	 * @return string
	 */
	public function get_param_or_fail(string $key):string{
	    if(!isset($this->get_params[$key])){
	        throw new \Talis\Exception\ParamNotFound($key);
	    }
	    return $this->get_params[$key];
	}
	
	
	/**
	 * the body->params 
	 * @return \stdClass
	 */
	public function getBodyParams():\stdClass{
		if(!isset($this->getBody()->params)){
		    $this->body->params = new \stdClass;
		}
		return $this->getBody()->params;
	}
	
    /**
     * The the body->params->key or default value | null
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
	public function getBodyParam(string $key,$default=null){
	    return $this->getBody()->params->$key ?? $default;
	}
	
	/**
	 * The the body->params->key
	 * This can be called if you are sure the param exists
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function getBodyParamExists(string $key){
	    return $this->getBody()->params->$key;
	}
	
	/**
	 * The the body->params->key
	 * Failes if no param found
	 * 
	 * @param string $key
	 * @throws \Talis\Exception\ParamNotFound
	 * @return mixed
	 */
	public function getBodyParamOrFail(string $key){
	    if(!isset($this->getBody()->params->$key)){
	        throw new \Talis\Exception\ParamNotFound($key);
	    }
	    return $this->getBody()->params->$key;
	}
	
	/**
	 * Add values/keys to the parameter section of the body->params
	 * @param string $k
	 * @param mixed $v
	 * @return mixed
	 */
	public function addToBodyParams(string $k,$v){
		return $this->getBodyParams()->$k = $v;		
	}

}
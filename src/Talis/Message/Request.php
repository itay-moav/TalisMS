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
	 */
	public function get_param(string $key,?string $default=null):?string{
	    return $this->get_params[$key] ?? $default;
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
     * the the body->params->key
     * 
     * @param string $k
     * @param mixed $default
     * @return mixed
     */
	public function getBodyParam(string $k,$default=null){
		return $this->getBody()->params->$k ?? $default;
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
	
	/**
	 * 
	 *TOBEDELETED 
	public function get_as_psr7():\GuzzleHttp\Psr7\ServerRequest{
	    return \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
	}*/
}
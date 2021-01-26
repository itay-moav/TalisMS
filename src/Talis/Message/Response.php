<?php namespace Talis\Message;
class Response{
	const RESPONSE_TYPE__RESPONSE   = 'response',
		  RESPONSE_TYPE__DEPENDENCY = 'dependency',
		  RESPONSE_TYPE__ERROR      = 'error'
	;
	
	/**
	 * @var array<string>
	 */
	private array $headers      = [];
    /**
     * @var ?\stdClass
     */
	private ?\stdClass $body    = null;
	/**
	 * @var aStatus
	 */
	private aStatus $status;
	
	/**
	 * @var string
	 */
	private string $message	    = '';

	/**
	 * @var string
	 */
	private string $type		= self::RESPONSE_TYPE__RESPONSE;
	
	/**
	 * @var mixed
	 */
	private $payload	        = null;
	
	/**
	 * 
	 */
	public function __construct(){
		$this->body = new \stdClass;
	}
	
	/**
	 * @return array<string>
	 */
	public function getHeaders():array{
	    return $this->headers;
	}
	
	/**
	 * @param string $header
	 * @return Response
	 */
	public function setHeader(string $header):Response{
	    $this->headers[] = $header;
	    return $this;
	}
    
	/**
	 * @param \stdClass $body
	 * @return \stdClass
	 */
	public function setBody(\stdClass $body):\stdClass{
	    return $this->body = $body;
	}
    
	/**
	 * @param aStatus $status
	 * @return aStatus
	 */
	public function setStatus(aStatus $status):aStatus{
		return $this->status = $status;
	}
	
	/**
	 * @return aStatus
	 */
	public function getStatus():aStatus{
		return $this->status;
	}
	
	/**
	 * @param string $msg
	 * @return string
	 */
	public function setMessage(string $msg):string{
		return $this->message = $msg;
	}
	
	/**
	 * @return string
	 */
	public function getMessage():string{
		return $this->message;
	}
	
	/**
	 * @param mixed $payload
	 * @return mixed
	 */
	public function setPayload($payload){
		return $this->payload = $payload;
	}
	
	/**
	 * @return mixed
	 */
	public function getPayload(){
	    if(!$this->payload){
	        $this->payload = new \stdClass;
	    }
		return $this->payload;
	}
	
	/**
	 * 
	 */
	public function markError():void{
		$this->type=self::RESPONSE_TYPE__ERROR;
	}
	
	/**
	 * 
	 */
	public function markDependency():void{
		$this->type=self::RESPONSE_TYPE__DEPENDENCY;
	}

	/**
	 * 
	 */
	public function markResponse():void{
		$this->type=self::RESPONSE_TYPE__RESPONSE;
	}
	
	/**
	 * @return string
	 */
	public function getResponseType():string{
		return $this->type;
	}
	
	/**
	 * Carefull, it rebuilds the body each time from it's parts
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Message\Response::getBody()
	 */
	public function getBody():\stdClass{
		$body = \Talis\commons\array_to_object([
		        'status'   => $this->getStatus(),
				'type'	   => $this->type,
		        'message'  => $this->getMessage()
		]);
		$body->payload = $this->getPayload();
		return $this->setBody($body);
	}
	
	/**
	 *
	 * @return string
	 */
	public function __toString():string{
	    $j = json_encode($this->getBody());
	    return $j ?: '' ;
	}
}

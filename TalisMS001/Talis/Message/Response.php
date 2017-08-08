<?php namespace Talis\Message;
class Response extends aMessage{
	const RESPONSE_TYPE__RESPONSE   = 'response',
		  RESPONSE_TYPE__DEPENDENCY = 'dependency',
		  RESPONSE_TYPE__ERROR      = 'error'
	;
	
	private 	$status       = null,
				$message	  = '',
				$type		  = self::RESPONSE_TYPE__RESPONSE,
				$payload	  = null
	;
	
	/**
	 * 
	 */
	public function __construct(){
		$this->body = new \stdClass;
	}

	public function setStatus(aStatus $status):aStatus{
		return $this->status = $status;
	}
	
	public function getStatus():aStatus{
		return $this->status;
	}
	
	public function setMessage(string $msg):string{
		return $this->message = $msg;
	}
	
	public function getMessage():string{
		return $this->message;
	}
	
	public function setPayload(?\stdClass $payload):?\stdClass{
		return $this->payload = $payload;
	}
	
	public function getPayload():?\stdClass{
		return $this->payload = $payload;
	}
	
	public function markError(){
		$this->type=self::RESPONSE_TYPE__ERROR;
	}
	
	public function markDependency(){
		$this->type=self::RESPONSE_TYPE__DEPENDENCY;
	}

	public function markResponse(){
		$this->type=self::RESPONSE_TYPE__RESPONSE;
	}
	
	public function getResponseType():string{
		return $this->type;
	}
	
	public function getBody():?\stdClass{
		$body = \Talis\commons\array_to_object([
				'type'	   => $this->type,
				'message'  => $this->message,
				'payload'  => ''
		]);
		$body->payload = $this->payload;
		return $this->setBody($body);
	}
	
}
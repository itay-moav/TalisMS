<?php namespace Talis\Message;
class Response{
	private 	$body 	      = null,
				$status       = null
	;
	
	/**
	 * 
	 */
	public function __construct(){
		$this->body = new \stdClass;
	}
	
	public function setBody(\stdClass $body):\stdClass{
		return $this->body = $body;
	}

	public function getBody():\stdClass{
		return $this->body;
	}

	public function setStatus(aStatus $status):aStatus{
		return $this->status = $status;
	}
	
	public function getStatus():aStatus{
		return $this->status;
	}
}
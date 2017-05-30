<?php namespace Talis\Message;
class Response extends aMessage{
	private 	$status       = null
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
}
<?php
/**
 * View class to handle mainly http errors
 */
class Response_HeadersOnly extends Response_Abstract{

	
	/**
	 * Echo headers
	 * Echo layouts
	 * Echo view file itself
	 */
	public function render(){
		//headers
		foreach($this->headers as $header) header($header);
		return $this;
	}
}


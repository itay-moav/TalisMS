<?php
/**
 * View class to handle mainly http errors
 */
class Response_PostRedirectGet extends Response_Redirect{

	public function __construct(Request_Default $request){
		parent::__construct($request);
		$this->headers[] = 'HTTP/1.1 303 See Other';
	}
}


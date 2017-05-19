<?php

/**
 * View class to handle mainly http errors
 */
class Response_Redirect extends Response_HeadersOnly{

	protected $url ='';
	
	public function __construct(Request_Default $request,$url=''){
		parent::__construct($request);
		$this->url = commons\url\home() . $url;
	}
	
	public function setUrl($url){
		$this->url = commons\url\home() . $url;
		return $this;
	}
	
	public function setOrgUrl($url){
		$this->url = commons\url\home_org() . $url;
		return $this;
	}
	
	public function setFullUrl($url){
		$this->url = $url;
		return $this;
	}

	/**
	 * Echo headers
	 * Echo layouts
	 * Echo view file itself
	 */
	public function render(){
		parent::render();
		dbgn('Redirecting: ' . $this->url);
		session_commit();
		header("Location: {$this->url}");
		exit;
	}
}


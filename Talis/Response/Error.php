<?php
/**
 * View class to handle mainly http errors
 */
class Response_Error extends Response_View{
	private $error_headers	= [	403 => 'HTTP/1.0 403 Forbidden',
								404 => 'HTTP/1.0 404 Not Found',
								500 => 'HTTP/1.0 500 Internal Server Error',
								1000 => 'HTTP/1.0 403 Forbidden', //no org access
								1005 => 'HTTP/1.0 403 Forbidden' // Action accessible in another organization
	];
	
	public function __construct($http_error_code){
		$this->layout		= [];
		$this->module		= 'general';
		$this->controller	= 'error';
		$this->action		= $http_error_code;
		$this->headers[]	= $this->error_headers[$http_error_code];
	}
	
	/**
	 * Echo headers
	 * Echo layouts
	 * Echo view file itself
	 */
	public function render(){
	    //headers
	    foreach($this->headers as $header) header($header);
	
	    //layouts before
	    foreach($this->layouts as $layout) $layout->renderBefore();
	
	    $view_file = VIEW_PATH . '/general/error/' . $this->action . '.php';
	    $r = include_once $view_file;
	    if(!$r) throw new Exception_ViewNotFound($view_file);
	
	    //layouts after
	    $render_after_layouts = array_reverse($this->layouts);
	    foreach($render_after_layouts as $layout) $layout->renderAfter();
	
	    return $this;
	}
}


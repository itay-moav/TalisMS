<?php
/**
 * Response class.
 * Rendering that should be the last step of any request.
 */
abstract class Response_Abstract{
	
	protected	$layouts 	= [],
				$module		= '',
				$sub_module	= '',
				$controller = '',
				$action		= '',
				$headers	= []
	;
	
	public function __construct(Request_Default $request){
		$this->module = $request->module;
		$this->sub_module = $request->sub_module;
		$this->controller = $request->controller;
		$this->action = $request->action;
		$this->init();
	}
	
	protected function init(){
	}
	
	/**
	 */
	abstract public function render();
	
	public function addLayout(){
		foreach(func_get_args() as $layout){
			$this->layouts[] = $layout;
		}
		return $this;
	}
	
	public function setLayout($layout){
		$this->layouts = [];
		$this->addLayout($layout);
		return $this;
	}
	
	public function setHeaders(array $headers){
		$this->headers = $headers;
		return $this;
	}
	
	/**
	 * You might want to change the view file to render,
	 * This will handle the last piece (action part)
	 */
	public function changeAction($new_view_file){
		$this->action = $new_view_file;
		return $this;
	}
}

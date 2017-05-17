<?php
/**
 * View class to handle mainly http errors
 */
class Response_GeneralView extends Response_View{
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
		
		$view_file = VIEW_PATH . '/general' . DIRECTORY_SEPARATOR . $this->controller . DIRECTORY_SEPARATOR . $this->action . '.php';
		$r = include_once $view_file;
		if(!$r) throw new Exception_ViewNotFound($view_file);
		
		//layouts after
		$render_after_layouts = array_reverse($this->layouts);
		foreach($render_after_layouts as $layout) $layout->renderAfter();
		
		return $this;
	}
}


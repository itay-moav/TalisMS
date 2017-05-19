<?php
/**
 * Response class to handle common views
 */
class Response_View extends Response_Abstract{
	static protected $_javascript	= '';
	
	static public function javascript($javascript=''){
		self::$_javascript .= $javascript;
		return self::$_javascript;		
	}
	
	static public function clean_javascript() {
	    self::$_javascript = '';
	}
	
	static public function logo(){
		return \commons\url\files() . '/files' . Organization_Current::logo();
	}
	
	/**
	 * Shortcut to include JS files
	 * 
	 * @param string $js_filename
	 * @param boolean $is_https
	 * @return string
	 */
	static public function include_js($js_filename){
		return '<script type="text/javascript" src="' .  \commons\url\js() . $js_filename . '"></script>';
	}
	
	/**
	 * Shortcut to include external JS files
	 * 
	 * @param string $js_filename
	 * @return string
	 */
	static public function include_external_js($js_filename){
		return '<script type="text/javascript" src="' . $js_filename . '"></script>';
	}

	/**
	 * Shortcut to include css files
	 * 
	 * @param string $css_filename
	 * @param boolean $is_https
	 * @return string
	 */
	static public function include_css($css_filename,$media='screen'){
		return '<link href="' .  \commons\url\css() . $css_filename . '" rel="stylesheet" media="' . $media . '" />';
	}
	
	/**
	 * Shortcut to include image files
	 * 
	 * @param string $image_filename
	 * @param boolean $is_https
	 * @return string
	 */
	static public function include_img($image_filename){
		return '<img src="' .  \commons\url\img() . $image_filename . '">';
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
		$view_file = VIEW_PATH . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . $this->sub_module . DIRECTORY_SEPARATOR . $this->controller . DIRECTORY_SEPARATOR . $this->action . '.php';
        if(!@include $view_file){
            throw new Exception_ViewNotFound($view_file);
        }
		
		//layouts after
		$render_after_layouts = array_reverse($this->layouts);
		foreach($render_after_layouts as $layout) $layout->renderAfter();
		
		return $this;
	}
}

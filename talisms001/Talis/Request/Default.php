<?php
/**
 * @author Itay Moav <itay.malimovka@gmail.com>
 * @license MIT
 *
 * Handles the data that comes from client.
 * Here be implemented cleanupas / filters / validations?
 */
class Request_Default{
	public		$module		= '',
				$sub_module	= '',
				$controller = '',
				$action		= ''
	;
	
	protected	$get	= [],
				$post	= [],
				$ActiveController	= null,
				$active_parts_config = []
	;
	
	/**
	 * Clean all un needed parts of the URI, and explode it into tokens
	 * that controller/action can be calculated by
	 */
	static public function get_request_parts($server){
		$home_url = explode('index.php',$server['SCRIPT_NAME'])[0];
		if($home_url == '/'){
			$home_url = 'bobo/';
			$server['REQUEST_URI'] = 'bobo' . $server['REQUEST_URI'];
		}
		$request_url = explode($home_url,$server['REQUEST_URI'])[1];
		$parts = explode('/',(explode('?',$request_url)[0]));
		$len = count($parts);
		if(!$parts[$len-1]){//in case we end url with /?
			unset($parts[$len-1]);
		}
		return $parts;
	}

	/**
	 * 
	 * @param array $get
	 * @param array $post
	 */
	public function __construct(array $get,array $post,array $active_parts = []){
		$this->get = $get;
		$this->post = $post;
		if($active_parts) $this->active_parts_config = $active_parts;
	}
	
	public function get($idx,$default=null){
		$this->get[$idx] = (isset($this->get[$idx]) && $this->get[$idx])?$this->get[$idx]:$default;
		return $this->get[$idx];
	}
	public function post($idx,$default=null){
		$this->post[$idx] = isset($this->post[$idx])?$this->post[$idx]:$default;
		return $this->post[$idx];
	}
	
	public function allGet(){
		return $this->get;
	}
	
	public function allPost(){
		return $this->post;
	}
	
	/**
	 * Builds and retrieves the requested controller.
	 * Assumes to be initiated with what parts are in this request
	 *
	 * @return Controller_Abstract
	 */
	public function getController(){
	    if(!$this->ActiveController){
	        $controller_name = '';
	        $sep_c = '';
	        foreach($this->active_parts_config as $part){
	            $controller_name .= $sep_c . ucfirst($this->$part);
	            $sep_c = '_';
	        }
	        try{
	           $this->ActiveController = new $controller_name($this);
	        }catch(Exception_ClassNotFound $e){
	            throw new Exception_ControllerNotFound($controller_name);
	        }
	    }
	    return $this->ActiveController;
	}
	
	/**
	 * According to the structure of URL, this proposes the view file path
	 *
	 * @return string view file proposed path
	 */
	public function proposedViewPath(){
	    $controller_name = '';
	    $file = '/';
	    $sep_f = '';
	    foreach($this->active_parts_config as $part){
	        $file.= $sep_f . $this->$part;
	        $sep_f = '/';
	    }
	    $file .= '/' . $this->action . '.php';
	    return $file;
	}
	
	/**
	 * Sets the controller to be the error controller.
	 * with page not found error type
	 * 
	 * @return Request
	 */
	public function notFound(){
		$this->action = 'error';
		$this->ActiveController = new General_Error($this,404);
		return $this;
	}
}//EOF CLASS
<?php
/**
 * @author Itay Moav <itay.malimovka@gmail.com>
 * @license MIT
 *
 * Handles the data that comes from client.
 * This is main web request for LMS 
 * 
 */
class Request_WebLms extends Request_Default{
    
    protected $active_parts_config = ['module','sub_module','controller'];
    
	/**
	 * Sets the controller to be the home controller.
	 *
	 * @return Request
	 */
	public function home(){
		$this->controller = 'site';
		$this->action = 'home';
		$this->ActiveController = new General_Site($this);
		return $this;
	}
	
	public function orgHomePage($path){
		$this->controller = 'site';
		$this->action = 'organization';
		$this->ActiveController = new General_Site($this);
		return $this;
	}	
}//EOF CLASS
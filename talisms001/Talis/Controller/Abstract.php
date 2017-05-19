<?php
/**
 * Base for full web Controller class
 */
 abstract class Controller_Abstract{
	/**
	 * @var array of depndencies for all actions in the controller.
	 */
	protected	$controller_dependency	= [];
	
	/**
	 * @var array of depndencies for each action.
	 */
	protected	$action_dependency	= [];
	
     
    /**
 	 * @var Response_Abstract
 	 */
	protected	$response		= null;
	
	/**
	 * @var Request_Default
	 */
	protected   $request		= null;
	
	/**
	 * Returns the Response object
	 */
	public function getResponse(){
		return $this->response;
	}

	/**
	 * Place holder - do not remove
	 */
	protected function init(){
	}
	
	protected function postDependencyInit(){
	}
	
	
	/**
	 * Check controller and action dependencies.
	 * 
	 * @return boolean whther failed or not. true all is good
	 */
	protected function checkIfFailedDependencies(){
	    $failed_dependencies_check = false;
	    foreach($this->controller_dependency as $dependency_class){
	        $D = new $dependency_class($this->request);
	        if(!$D->validate_dependency()){
	            $this->response = $D->act_fail();
	            $failed_dependencies_check = get_class($D);
	            break;
	        }
	    }
	    if(isset($this->action_dependency[$this->request->action]) && !$failed_dependencies_check){
	        foreach($this->action_dependency[$this->request->action] as $dependency_class){
	            $D = new $dependency_class($this->request);
	            if(!$D->validate_dependency()){
	                $this->response = $D->act_fail();
	                $failed_dependencies_check = get_class($D);
	                break;
	            }
	        }
	    }
	    
	    return $failed_dependencies_check;
	}
	
	/**
	 * Changes the action on both the response and the request
	 * @param unknown $new_action
	 */
	public function changeAction($new_action){
	    $this->response->changeAction($new_action);
	    $this->request->action = $new_action;
	    return $this;
	}
	public function failedDependency(){
	    return $this;
	}
}
<?php
/**
 * Base for full web Controller class
 */
 abstract class Controller_FullWeb extends Controller_Abstract{
	/**
	 * @var array $controller_task_dependencies list of TASK__* user must have to access this controller.
	 * 											Empty means open for all
	 */
	protected	$controller_task_dependencies	= [];
	
	/**
	 * @var array $action_task_dependencies		List of dependencies for each action, ON TOP of the ones in 
	 *											the $controller_task_dependencies.
	 *											Looks like:
	 *											['method_name' => [TASK__*,TASK__*...],
	 *											 'method_name' => [TASK__*,TASK__*...], ..
	 *											]
	 */
	protected	$action_task_dependencies		= [];
     
    /**
 	 * @var Request_Default $request
 	 */
 	protected	$static_pages	= ['index' => 'title of index'],
				/**
				 * @var array action to use what response/view class
				 */
				$action_response	= [] //If an action needs a specific response/view type, even in a static request, this array is where you will do it
									 //'action'=>'response/view class name'
	;

	/**
	 * Existing actions don't get here.
	 * If it gets here, it might be a valid no-action page,
	 * or a mistake (404)
	 * 
	 * @param string $name
	 * @param array $arguments this is the __call signature, so it s here, although not used currently
	 */
	public function __call($name, $arguments){
	    if(!isset($this->static_pages[$name])){
			throw new Exception_ActionNotFound($this,$name);
		}else{
			Layout::inject(Layout::TITLE,$this->static_pages[$name]);
		}
		return $this;
    }
    
	/**
	 * @param unknown_type $request
	 * @throws Exception_ActionNotFound
	 */
	public function __construct(Request_Default $request){
		$this->request = $request;
		
		//check user has access (has the task)
		if(!$this->canAccessThis($request->action)){
			throw new Exception_Auth_NotAllowed(get_class($this),$request->action);
		}
		
		//some custom init code
		$this->init();
		
		//choose your response - default behaviour, can be over-ridden in any action
		$this->initResponse();
		
		//post dependencies init
		$this->postDependencyInit();
	}
	
	/**
	 * Checks against current user if he satisfies ALL dependencies
	 * to access requested action
	 */
	protected function canAccessThis($action){
	    //no dependencies, no worries
	    if($this->controller_task_dependencies == [] &&
	        (!isset($this->action_task_dependencies[$action]) || $this->action_task_dependencies[$action] == [])){
	        return true;
	    }
	
        $tasks = $this->controller_task_dependencies;
        if(isset($this->action_task_dependencies[$action])){
            $tasks = array_merge($tasks, $this->action_task_dependencies[$action]);
        }
        
        return User_Current::canDoTasks($tasks);
    }
    
    /**
     *  Checks that the current user is able to display the task because of access in another organization
     */
    protected function canDisplayThis($action){
		//no dependencies, no worries
		if($this->controller_task_dependencies == [] &&
		(!isset($this->action_task_dependencies[$action]) || $this->action_task_dependencies[$action] == [])){
			return true;
		}
		
		$tasks = $this->controller_task_dependencies;
		if(isset($this->action_task_dependencies[$action])){
			$tasks = array_merge($tasks, $this->action_task_dependencies[$action]);
		}
		
		return User_Current::canDisplayTasks($tasks);
    }

	/**
	 * 
	 * @return Controller_FullWeb
	 */
	protected function initResponse(){
		//If nothing was found, set the default response
		$failed_the_dependency = $this->checkIfFailedDependencies();
		if(!$failed_the_dependency){
			(!$this->response)?$this->setDefaultResponseLayout():null;//If I have some default response from someware else, I do nothing (May be a tailored init or constructior)
		}else{
		    info('Failed dependency ' . $failed_the_dependency);
		    $this->changeAction('failedDependency');
		}
		return $this;
	}
	
	/**
	 * If no dependency failed, will use this default method to build the response
	 * 
	 * @return Controller_FullWeb
	 */
	protected function setDefaultResponseLayout(){
		if(isset($this->action_response[$this->request->action])){
			$class_name = 'Response_' . $this->action_response[$this->request->action];
			$this->response = new $class_name($this->request);
		}else{
			$this->response = new Response_View($this->request);
		}

		$this->setDefaultLayout();
		return $this;		
	}
	
	protected function setDefaultLayout(){
		//Choose weather to use the login or logout layout.
		require_once LAYOUT_PATH . '/PublicL.php';
		$this->response->addLayout(new PublicL($this->request));
	}
}
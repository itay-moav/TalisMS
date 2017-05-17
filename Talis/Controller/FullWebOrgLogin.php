<?php
require_once LAYOUT_PATH . '/LoginL.php';
/**
 * Full web Controller class for member pages only
 */
 abstract class Controller_FullWebOrgLogin extends Controller_FullWeb{

 	/**
 	 * @param Request_Default $request
 	 */
 	public function __construct(Request_Default $request){
 	    if(!User_Current::id()){
 	        throw new Exception_Auth_NotLoggedin;
 	    }
		//check user has access to this org
		$current_organization_path = Organization_Current::path();
		if(!isset($current_organization_path) || !User_Current::has_org($current_organization_path)){
			throw new Exception_Auth_NoOrgAccess;//since the general nature of this, I'll handle this in Talis
		}
		
		//if user has switched his org, reload his roles.
		if(User_Current::current_org() != Organization_Current::id()){
			User_Current::login(User_Current::id(),User_Current::pupetMasterId(),Organization_Current::id());
		}

 	 	//check user has access to this action
		if(!$this->canAccessThis($request->action)){
			$can_display = $this->canDisplayThis($request->action);
			if(!$can_display){
				throw new Exception_Auth_NotAllowed(get_class($this),$request->action);
			}else{
				throw new Exception_Auth_WrongOrgTask(get_class($this),$request->action, $can_display);
			}
		}
		
		$this->request = $request;
		//some custom init code
		$this->init();
		
		$this->initResponse();
		
		$this->postDependencyInit();
	}
	
	protected function setDefaultLayout(){
		//Choose weather to use the login or logout layout.
		$this->response->addLayout(new LoginL($this->request));
	}
}
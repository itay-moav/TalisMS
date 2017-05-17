<?php
/**
 * This dependency will redirect to login page
 * @author itaymoav
 */
class Controller_Dependency_Login extends Controller_Dependency_Action{
	/**
	 * 
	 */
	public function validate_dependency() {
		return User_Current::id();
	}

	/** 
	 * @return Response_Redirect to home folder (in org, if available)
	 */
	public function act_fail() {
		return new Response_Redirect($this->request);
	}

	/**
	 * @return Request_Default
	 */
	public function act_success() {
		return $this->request;		
	}
}
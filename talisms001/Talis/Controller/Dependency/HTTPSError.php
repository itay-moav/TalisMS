<?php
/**
 * This dependency will error out if no https 
 * @author itaymoav
 */
class Controller_Dependency_HTTPSError extends Controller_Dependency_Action{
	/**
	 * 
	 */
	public function validate_dependency() {
		return is_https();
	}

	/** 
	 * @return Response_Redirect to home folder (in org, if available)
	 */
	public function act_fail() {
	    throw new Exception_Url_NotHTTPS;
	}

	/**
	 * @return Request_Default
	 */
	public function act_success() {
		return $this->request;		
	}

		
}
<?php
/**
 * This dependency will redirect to version HTTPS of the request 
 * @author itaymoav
 */
class Controller_Dependency_NotLogin extends Controller_Dependency_Action{
	/**
	 * 
	 */
	public function validate_dependency() {
		return !User_Current::id();
	}

	/** 
	 * @return Response_Redirect to home folder (in org, if available)
	 */
	public function act_fail() {
		//$url = org_url(FORCE_HTTPS) . '/curriculum/myeducation/courses/current/';//home folder, as https
	    $url = url(true, DONT_FORCE_SCHEMA,'www');// To handle the new registration process.
	    
		$response = new Response_Redirect($this->request);
		$response->setFullUrl($url);
		return $response;
	}

	/**
	 * @return Request_Default
	 */
	public function act_success() {
		return $this->request;		
	}
}
<?php
/**
 * This dependency will fail if the requested page has no org component to it
 *  
 * TODO - I really need a way to make this more dynamic redirect url
 * 
 * @author itaymoav
 */
class Controller_Dependency_OrgUrl extends Controller_Dependency_Action{
	/**
	 * 
	 */
	public function validate_dependency() {
	   return Organization_Current::isOrg();
	}

	/** 
	 * @return Response_Redirect to home folder (in org, if available)
	 */
	public function act_fail() {
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
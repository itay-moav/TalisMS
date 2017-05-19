<?php
/**
 * This dependency will redirect to version HTTPS of the request 
 * @author itaymoav
 */
class Controller_Dependency_HTTPSRedirect extends Controller_Dependency_Action{
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
		$url = org_url(FORCE_HTTPS);//home folder, as https
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
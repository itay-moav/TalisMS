<?php
abstract class Controller_Dependency_Action{
	/**
	 * @var Request_Default
	 */
	protected $request = null;
	/**
	 * @param Response_Abstract $response
	 */	
	public function __construct(Request_Default $request){
		$this->request = $request;		
	}
	/**
	 * @param Response_Abstract $response
	 * 
	 * @return boolean if dependency was satisfied or not
	 */
	abstract public function validate_dependency();
	/**
	 * Action to do on failure to satisfy depndency
	 * Either returns a new Response or throws an exception
	 * 
	 * @return Response_Abstract
	 */
	abstract public function act_fail();
	/**
	 * Action to do on success to satisfy dependency
	 * 
	 * @return Response_Abstract
	 */
	abstract public function act_success();
}

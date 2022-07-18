<?php namespace Talis\Chain\Errors;

/**
 * User is not allowed to run this API class
 * 
 * @author Itay Moav
 * @date 2022-07-17
 */
class AccessForbbiden extends aError{
	protected int $http_code = 403;
	
	/**
	 * {@inheritDoc}
	 * @see \Talis\Chain\Errors\aError::format_human_message()
	 */
	protected function format_human_message():string{
		$api_uri = $this->Request->getUri();
		return "Current user is not allowed in {$api_uri}.";
	}
}

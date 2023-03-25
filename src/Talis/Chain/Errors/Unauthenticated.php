<?php namespace Talis\Chain\Errors;

/**
 * Unauthenticated request
 * 
 * @author Itay Moav
 * @date 2022-12-01
 */
class Unauthenticated extends aError{
	protected int $http_code = 401;
	
	/**
	 * {@inheritDoc}
	 * @see \Talis\Chain\Errors\aError::format_human_message()
	 */
	protected function format_human_message():string{
		$api_uri = $this->Request->getUri();
		return "Unauthenticated request for {$api_uri}.";
	}
}

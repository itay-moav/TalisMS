<?php namespace Talis\Chain\Errors;

/**
 * API class was not found for the input route
 * 
 * @author Itay Moav
 * @date 2017-05-23
 */
class BLLinkMissingInChain extends aError{
	protected int $http_code = 500;
	
	protected function format_human_message():string{
		$api_uri = $this->Request->getUri();
		return "Missing BL link for URI {$api_uri}. This request chain was not properly configured. The BL Link is missing, probably in the API class.";
	}
}

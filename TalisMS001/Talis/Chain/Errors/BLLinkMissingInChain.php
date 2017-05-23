<?php namespace Talis\Chain\Errors;

/**
 * API class was not found for the input route
 * 
 * @author Itay Moav
 * @date 2017-05-23
 */
class BLLinkMissingInChain extends aError{
	protected $http_code = 500;
	
	protected function format_human_message():string{
		\Talis\Logger\fatal("Missing BL link for URI {$this->error_params[0]}");
		return "This request chain was not properly configured. The BL Link is missing, probably in the API class.";
	}
}

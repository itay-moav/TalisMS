<?php namespace Talis\Chain\Errors;

/**
 * API class was not found for the input route
 * 
 * @author Itay Moav
 * @date 2017-05-23
 */
class ApiNotFound extends a400Info{
	protected $http_code = 404;
	
	protected function format_human_message():string{
	    $params_msg = $this->Request ? $this->Request->getUri() : 'unknown';
		return "Api resource for [{$params_msg}] can not be found!";
	}
}

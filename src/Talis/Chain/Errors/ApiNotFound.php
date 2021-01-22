<?php namespace Talis\Chain\Errors;

/**
 * API class was not found for the input route
 * 
 * @author Itay Moav
 * @date 2017-05-23
 */
class ApiNotFound extends a400Info{
	protected int $http_code = 404;
	
	protected function format_human_message():string{
	    //TOBEDELETED202102 $params_msg = $this->Request ? $this->Request->getUri() : 'unknown';
	    $api_uri = $this->Request->getUri();
		return "Api resource for [{$api_uri}] can not be found!";
	}
}

<?php namespace Talis\Chain\Errors;

/**
 * API class was not found for the input route
 * 
 * @author Itay Moav
 * @date 2017-05-23
 */
class ApiNotFound extends aError{
	protected $http_code = 404;
	
	protected function format_human_message():string{
		return "Api resource for [{$this->params[0]}] can not be found!";
	}
}

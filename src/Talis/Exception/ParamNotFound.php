<?php namespace Talis\Exception;
/**
 * Could not find the param in the Request
 */
class ParamNotFound extends \Exception{
    /**
     * @param string $param_name
     */
	public function __construct(string $param_name){
		parent::__construct("Param [{$param_name}] not found!");
	}
}
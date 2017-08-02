<?php namespace Talis\Exception;
/**
 * Could not find class for inclusion
 */
class UnknownUser extends \Exception{
	public function __construct($user_id){
		parent::__construct("Unknown user id in system [{$user_id}]");
	}
}
<?php namespace Talis\Exception;
/**
 * Could not find class for inclusion
 */
class BadUri extends \Exception{
	public function __construct($uri){
		parent::__construct("[{$uri}] can not be found!");
	}
}
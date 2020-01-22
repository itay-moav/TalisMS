<?php namespace Talis\Exception;
/**
 * Could not find class for inclusion
 */
class ClassNotFound extends \Exception{
	public function __construct($file){
		parent::__construct("failed to include [{$file}]");
	}
}
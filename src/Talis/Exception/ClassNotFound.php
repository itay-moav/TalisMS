<?php namespace Talis\Exception;
/**
 * Could not find class for inclusion
 */
class ClassNotFound extends \Exception{
    /**
     * @param string $file
     */
	public function __construct(string $file){
		parent::__construct("failed to include [{$file}]");
	}
}
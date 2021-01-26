<?php namespace Talis\Exception;
/**
 * Could not find class for inclusion
 */
class FileNotFound extends \Exception{
    /**
     * @param string $file
     */
	public function __construct(string $file){
		parent::__construct("[{$file}] not found!");
	}
}
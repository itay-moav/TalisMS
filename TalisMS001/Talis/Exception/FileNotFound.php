<?php namespace Talis\Exception;
/**
 * Could not find class for inclusion
 */
class FileNotFound extends \Exception{
	public function __construct($file){
		parent::__construct("[{$file}] not found!");
	}
}
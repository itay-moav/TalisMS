<?php namespace Talis\Exception;
/**
 * Could not find class for inclusion
 */
class BadUri extends \Exception{
    /**
     * 
     * @param string $uri
     */
	public function __construct($uri){
	    parent::__construct("Badboy URI [{$uri}]");
	}
}
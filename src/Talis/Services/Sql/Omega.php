<?php namespace Talis\Services\Sql;
use function Talis\Logger\dbgr;
/**
 * Wrapper for easy access to stored procedures in Omega Supreme.
 * This depends adding the auto completion file to the Eclipse language directory for core php
 * 
 * @author itaymoav
 *
 */
class Omega{
	/**
	 * 
	 * @var \Talis\Services\Sql\MySqlClient
	 */
    private $DB;
    
    /**
     * DO NOT CHANGE THIS RETURN TYPE. It allows for the auto completion to work with RCOM
     * @return \SP
     */
    static public function Supreme(string $db_name=''):Omega{
        return new self($db_name);
    }
    
    /**
     * 
     * @param string $db_name
     */
    public function __construct(string $db_name=''){
    	if($db_name){
    		$this->DB = \Talis\Services\Sql\Factory::getConnectionMySQL($db_name);
    	}
    	$this->DB = \Talis\Services\Sql\Factory::getDefaultConnectionMySql();
    }
    
    /**
     * Close cursor for buggy Stored Procedure calls
     * @return \Talis\Services\Sql\MySqlClient
     */
    public function closeCursor(){
    	return $this->DB->closeCursor();
    }
    
    /**
     * @return \SP
     */
    public function s(){
    	return $this;
    }
    
    /**
     * @param string $sp
     * @param array $args
     * 
     * @return \Talis\Services\Sql\MySqlClient
     */
    public function __call($sp,$args){
        $sp = str_replace('__','.',$sp);
        return $this->DB->callArr($sp, $args);
    }
}


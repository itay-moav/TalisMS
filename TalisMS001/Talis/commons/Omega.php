<?php namespace Talis\commons;
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
     * @return SP
     */
    static public function db(string $db_name):Omega{
        return new self($db_name);
    }
    
    public function __construct($db_name){
    	$this->DB = mysql_db($db_name); //This assume connection was already established for this db name 
    }
    
    /**
     * Close cursor for buggy Stored Procedure calls
     * @return \Talis\Services\Sql\MySqlClient
     */
    public function closeCursor(){
    	return $this->DB->closeCursor();
    }
    
    /**
     * @param string $sp
     * @param array $args
     * 
     * @return \Talis\Services\Sql\MySqlClient
     */
    public function __call($sp,$args){
        $pos = strpos($sp,'_');
        $sp[$pos]='.';
        return $this->DB->callArr($sp, $args);
    }
}


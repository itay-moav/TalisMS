<?php
/**
 * Wrapper for easy access to stored procedures in Omega Supreme.
 * This depends adding the auto completion file to the Eclipse language directory for core php
 * 
 * @author itaymoav
 *
 */
class Omega{
    static private $DB;
    
    /**
     * @return SP
     */
    static public function rd(){
       self::$DB = rddb();
       return new self;
    }

    /**
     * @return SP
     */
    static public function rw(){
        self::$DB = rwdb();
        return new self;
    }
    
    /**
     * Close cursor for buggy Stored Procedure calls
     * @return mixed Data_MySQL_DB if set else false
     */
    static public function closeCursor(){
    	return isset(self::$DB)?self::$DB->closeCursor():false;
    }
    
    /**
     * @param string $sp
     * @param array $args
     * 
     * @return Data_MySQL_DB
     */
    public function __call($sp,$args){
        $pos = strpos($sp,'_');
        $sp[$pos]='.';
        return self::$DB->callArr($sp, $args);
    }
}


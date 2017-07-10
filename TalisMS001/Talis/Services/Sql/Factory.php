<?php namespace Talis\Services\Sql;
use function Talis\Logger\dbgn;
use function Talis\Logger\fatal;

/**
 * Factory class to get a SQL client 
 * 
 * @author Itay Moav
 */
class Factory{
	
	const CONNECTION_NAME__READ  = 'read',
              CONNECTION_NAME__WRITE = 'write'
	;

	/**
   	 * @var array of registered (created with factory) connections.
         */
	static private $registered_connections = [];

	/**
   	 * @return Talis\Services\Sql\MySqlClient
 	 */
	static public getConnectioniMySQL(string $connection_name,array $config=[]){
		if(isset(self::$registered_connections[$connection_name])) return self::$registered_connections[$connection_name];

		if($config == []) throw new LogicException('You must pass a config array to get a connection');		

		return self::$registered_connections[$connection_name] = new MySqlClient($connection_name,$config);
	}

	static public function unregister(string $connection_name){
		unset(self::registered_connections[$connection_name));
	}

        /**
         * @return Talis\Services\Sql\MySqlClient
         */
	static public getReadConn(){
		return self::getConnectioniMySQL(self::CONNECTION_NAME__READ,app_env()['database']['slave']);
	} 

        /**
         * @return Talis\Services\Sql\MySqlClient
         */
        static public getWriteConn(){
                return self::getConnectioniMySQL(self::CONNECTION_NAME__WRITE,app_env()['database']['master']);
        }
}
	

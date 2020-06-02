<?php namespace Talis\Services\Sql;

/**
 * Factory class to get a SQL client
 *
 * @author Itay Moav
 */
class Factory {
	const CONNECTION_NAME__READ = 'mysql_read', 
		  CONNECTION_NAME__WRITE = 'mysql_write'
	;
	
	/**
	 *
	 * @var array of registered (created with factory) connections.
	 */
	private static $registered_connections = [ ];
	
	/**
	 *
	 * @return \Talis\Services\Sql\MySqlClient
	 */
	static public function getConnectionMySQL(string $connection_name, array $config = []) {
		if (isset ( self::$registered_connections [$connection_name] ))
			return self::$registered_connections [$connection_name];
		
		if ($config == [ ])
			throw new \LogicException ( 'You must pass a config array to get a connection' );
		
		return self::$registered_connections [$connection_name] = new MySqlClient ( $connection_name, $config );
	}
	
	static public function getDefaultConnectionMySql():MySqlClient{
		if(!self::$registered_connections){
			throw new \LogicException ('You must initilize one connection to use this method');
		}
		//dbgr('connections',self::$registered_connections);
		return reset(self::$registered_connections);
	}

	/**
	 * Check if a valid name was passed, if not will return the default connection
	 * 
	 * @param string $connection_name
	 * @param array $config
	 * @return \Talis\Services\Sql\MySqlClient
	 */
	static public function getConnectionOrDefaultMySQL(string $connection_name='', array $config = []):MySqlClient{
		if($connection_name){
			return self::getConnectionMySQL($connection_name,$config);
		}
		return self::getDefaultConnectionMySql();
	}
	
	/**
	 *
	 * @param string $connection_name        	
	 */
	static public function unregister(string $connection_name) {
		unset ( self::$registered_connections [$connection_name] );
	}
	
	/**
	 *
	 * @return \Talis\Services\Sql\MySqlClient
	 */
	static public function getReadConn() {
		return self::getConnectionMySQL ( self::CONNECTION_NAME__READ, app_env () ['database'] ['mysql_slave'] );
	}
	
	/**
	 *
	 * @return \Talis\Services\Sql\MySqlClient
	 */
	static public function getWriteConn() {
		return self::getConnectionMySQL ( self::CONNECTION_NAME__WRITE, app_env () ['database'] ['mysql_master'] );
	}
	
	/**
	 * 
	 * @return array
	 */
	static public function getDebugInfo():array{
		$ret = [];
		foreach(self::$registered_connections as $name=>$connection){
			$ret[$name] = $connection->getDebugInfo();
		}
		return $ret;
	}
}
	

<?php namespace Talis\Services\Redis;
use function \Talis\Logger\dbgn;
use function \Talis\Logger\error;
use function \Talis\Logger\dbgr;
/**
 * Talis Wrapper for the Redis connection
 *
 * @author Itay Moav
 */
class Client {
	/**
	 *
	 * @var \Redis
	 */
	private static $MyRedis = null;
	
	/**
	 *
	 * @var Data_Redis_iKeyBoss key
	 *     
	 * @return Data_Redis_Client with a specific key
	 */
	static public function getInstance(array $config,iKeyBoss $key, iDataBuilder $DataBuilder = null) { // was create
		if (! self::$MyRedis) {
			
			$host = $config['host'];
			dbgn ( "connecting to redis: [{$host}]" );
			
			try {
				self::$MyRedis = new \Redis ();
				self::$MyRedis->connect ( $host );
				self::$MyRedis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
			} catch ( \Exception $E ) {
				error ( $E . ' IP: ' . $host, 1 );
				error ( 'Using NoRedis', 0 );
				self::$MyRedis = new NoRedis ();
			}
		}
		return new Client ( $key, $DataBuilder,$config['verbosity']);
	}
	
	/**
	 * Close Redis server and empty Redis instance
	 */
	static public function close() {
		dbgn ( 'disconnecting from redis' );
		if (self::$MyRedis) {
			self::$MyRedis->close ();
			self::$MyRedis = null;
		}
	}
	private function __construct(iKeyBoss $key, iDataBuilder $DataBuilder = null, $error_verbosity = 0) {
		$this->key = $key . ''; // activate to string
		$this->DataBuilder = $DataBuilder;
		$this->error_verbosity = $error_verbosity;
	}
	
	/**
	 *
	 * @var string the key this class will work on.
	 */
	private $key = '', 

	/**
	 *
	 * @var iDataBuilder
	 */
	$DataBuilder = NULL,
	
	/**
	 * What to output in logs
	 */
	$error_verbosity = 0;
	
	/**
	 * 
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}
	
	/**
	 * Wrapper for the redis class
	 * To get dbg and error recovery
	 *
	 * @param string $name
	 *        	Redis class method name
	 * @param array $arguments        	
	 */
	public function __call($method_name, array $arguments = []) {
		if ($this->error_verbosity) dbgn ( "Redis SUNSET" );
		
		$arguments = array_merge ([$this->key], $arguments );
		if ($this->error_verbosity)
			dbgr ( $method_name, $arguments );
		$r = false;
		try {
			$r = call_user_func_array ( array (
					self::$MyRedis,
					$method_name 
			), $arguments );
		} catch ( Exception $e ) { // TODO if a builder was supplied, use the builder to return the data
			error_monitor ( $e, 1 );
			error_monitor ( 'We had a Redis issue, see before msg', 0 );
			dbgn ( 'Redis call failed' );
			return false;
		}
		if ($this->error_verbosity > 1) dbgr ( 'RESULTS FROM MY REDIS', $r );
		
		if (! $r && in_array ( $method_name, [ 
				'get' 
		] ) && $this->DataBuilder) {
			if ($this->error_verbosity)
				dbgn ( 'building data' );
			$r = $this->DataBuilder->build ();
			if ($r)
				$this->set ( $r ); // Don't know, maybe no need of a condition here.
		}
		return $r;
	}
	public function serialize() {
		self::$MyRedis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
	}
	public function dontSerialize() {
		self::$MyRedis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
	}
	
	/**
	 * Read Redis.io and redisphp to understand how pattern works
	 *
	 * @param integer $cursor
	 *        	(resource)
	 * @param string $pattern        	
	 */
	public function sscan(&$cursor, $pattern = false) {
		self::$MyRedis->setOption ( \Redis::OPT_SCAN, \Redis::SCAN_RETRY ); // do not bring empty results
		if ($pattern) {
			return self::$MyRedis->sscan ( $this->key, $cursor, $pattern );
		} else {
			return self::$MyRedis->sscan ( $this->key, $cursor );
		}
	}
}

/**
 * In case we do not have Redis available, use this as pseudo connection.
 * Will always return false
 *
 * @author itaymoav
 */
class NoRedis {
	public function __call($name, array $arguments) {
		return false;
	}
	public function close() {
	}
}
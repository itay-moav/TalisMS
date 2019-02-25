<?php namespace Talis\Services\Redis;
/**
 * Talis Wrapper for the Redis connection
 *
 * @author Itay Moav
 */
class Client{
    /**
     * @var \Redis
     */
    static private $MyRedis = null;
    
    /**
     * @var int the db name, defaults to 0.
     */
    static private $current_MyRedis_db = 0;
    
    /**
     * @var aKeyBoss key
     *
     * @return \Talis\Services\Redis\Client with a specific key
     */
    static public function getInstance(array $config,aKeyBoss $key,$logger,iDataBuilder $DataBuilder=null){
        if(!self::$MyRedis){
            $logger->debug("=================== Redis CONNECT [{$config['host']}] ===================\n");
            self::$MyRedis = new \Redis;
            self::$MyRedis->connect($config['host']);
            self::$MyRedis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        }
        return new \Talis\Services\Redis\Client($key,$logger,$DataBuilder);
    }
    
    /**
     * Close Redis server and empty Redis instance
     */
    static public function close($logger){
        $logger->debug("=================== disconnecting from redis ===================\n");
        if (self::$MyRedis) {
            self::$MyRedis->close();
            self::$MyRedis = null;
        }
    }
    
    /**
     * 
     * @param aKeyBoss $key
     * @param mixed $logger TODO until we migrate all to Talis, we need to leave this typeless
     * @param iDataBuilder $DataBuilder
     */
    private function __construct(aKeyBoss $key,$logger,iDataBuilder $DataBuilder=null){
        $this->key         = $key;
        $this->logger      = $logger;
        $this->DataBuilder = $DataBuilder;
    }
    
    /**
     * @var aKeyBoss the key this class will work on.
     */
    private $key,
    
    /**
     * @var iDataBuilder
     */
            $DataBuilder = NULL,
            
    /**
     * @var \Talis\Logger\Streams\aLogStream
     */        
            $logger
    ;   
    
    /**
     * Makes sure we run the Redis command on the right DB.
     */
    protected function call_db_init(){
        $this->logger->debug("=================== Redis SUNSET ===================\n");
        if(self::$current_MyRedis_db != $this->key->get_db()){
            dbgn('SELECT (new db) [' . $this->key->get_db() . ']');
            $res = self::$MyRedis->select($this->key->get_db());
            dbgn($res);
            self::$current_MyRedis_db = $this->key->get_db();
        }
    }

    /**
     * Wrapper for the redis class
     * To get dbg and error recovery
     *
     * @param string $name Redis class method name
     * @param array $arguments
     */
    public function __call($method_name , array $arguments=[]){
        
        $this->call_db_init();
        
        $arguments = array_merge([$this->key->key_as_string()],$arguments);
        $this->logger->debug("===== Redis: {$method_name}\n" . print_r($arguments,true));
        $r = call_user_func_array([self::$MyRedis, $method_name], $arguments);
        $this->logger->debug("===== Redis: RESULTS FROM MY REDIS\n" . print_r($r,true));
        
        if(!$r && in_array($method_name,['get']) && $this->DataBuilder){
            $this->logger->debug("===== Redis: Building data\n");
            $r = $this->DataBuilder->build();
            if($r) {
                $this->DataBuilder->ttl()?$this->set($r,$this->DataBuilder->ttl()):$this->set($r);
            }
        }
        return $r;
    }
    
    /**
     * IF data has to be serilized (primitives should not be, this take memeory and time)
     */
    public function serialize(){
        self::$MyRedis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
    }
    
    /**
     * Prevent key data serialization
     */
    public function dontSerialize(){
        self::$MyRedis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
    }
    
    /**
     * setOption wrapper
     * 
     * @param int $key use \Redis constants
     * @param int $value  use \Redis constants
     */
    public function setOption(int $key, int $value){
        self::$MyRedis->setOption($key,$value);
    }
    
    /**
     * MUST STAY HERE, due to by ref not passing to __call properly
     * 
     * Read Redis.io and redisphp to understand how pattern works
     *
     * @param integer $cursor (resource)
     * @param string  $pattern
     */
    public function sscan(&$cursor,$pattern=false){
        $this->call_db_init();
        
        $this->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);//do not bring empty results
        dbgr('Redis: SSCAN',['cursor' => $cursor,'pattern' => $pattern]);
        if($pattern){
            return self::$MyRedis->sscan($this->key->key_as_string(),$cursor,$pattern);
        }else{
            return self::$MyRedis->sscan($this->key->key_as_string(),$cursor);
        }
    }
}

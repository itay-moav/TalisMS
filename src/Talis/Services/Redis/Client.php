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
     * TODO how to do different DBs?
     * 
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
        $this->key         = $key . '';//activate __to_string
        $this->logger      = $logger;
        $this->DataBuilder = $DataBuilder;
    }
    
    /**
     * @var string the key this class will work on.
     */
    private $key		 = '',
    
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
     * Wrapper for the redis class
     * To get dbg and error recovery
     *
     * @param string $name Redis class method name
     * @param array $arguments
     */
    public function __call($method_name , array $arguments=[]){
        $this->logger->debug("=================== Redis SUNSET ===================\n");
        $arguments = array_merge(array($this->key),$arguments);
        $this->logger->debug("===== Redis: {$method_name}\n" . print_r($arguments,true));
        $r = call_user_func_array(array(self::$MyRedis, $method_name), $arguments);
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
     * TODO move to the correct ClientMask object !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!11111111111111111111111111111111111111111111111111111111111111111111111111
     * 
     * Read Redis.io and redisphp to understand how pattern works
     *
     * @param integer $cursor (resource)
     * @param string $pattern
     */
    public function sscan(&$cursor,$pattern=false){
        self::$MyRedis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);//do not bring empty results
        if($pattern){
            return self::$MyRedis->sscan($this->key,$cursor,$pattern);
        }else{
            return self::$MyRedis->sscan($this->key,$cursor);
        }
    }
}

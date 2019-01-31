<?php namespace Talis\Services\Redis;
/**
 * This class is where u construct the Client specific for a key 
 * with all it's constraints.
 * 
 * @author itaymoav
 */
abstract class aKeyBoss{
    
    /**
     * @var \Talis\Services\Redis\aClientMask
     */
    static protected $KeyClient = null;
    
    /**
     * @var char key field separator
     */
    const  FIELD_SEPARATOR = ':';
    
    public function __construct(string $var_key = '',iDataBuilder $builder = null){
        $this->var_key = $var_key;
        $this->builder = $builder;
    }
    
    /**
     * The key of current redis element
     * @var string
     */
    protected $var_key = '';
    
    /**
     * Object that populate the Redis key if no data found
     * 
     * @var iDataBuilder
     */
    protected $builder = null;

    /**
     * Gets a Redis client wrapped in a Mask
     * 
     * @return \Talis\Services\Redis\aClientMask
     */
    public function get_client():\Talis\Services\Redis\aClientMask{
        if(static::$KeyClient){
            return static::$KeyClient;
        }
        $r = Client::getInstance(['host'=>$this->host()],$this,$this->logger(),$this->builder);
        if($this->should_i_serilize()){
            $r->serialize();
        } else {
            $r->dontSerialize();
        }
        static::$KeyClient = $this->get_redis_mask($r);
        return static::$KeyClient;
    }
    
    /**
     * set to true will cause data sent to Redis to be serilized.
     * more expensive.
     */
    protected function should_i_serilize():bool{
        return false;
    }
    
    /**
     * Returns the host IP, I would create another abstract on
     * top of this class to fill all the default values for 
     * all abstract methods.
     */
    abstract protected function host():string;
    
    /**
     * Returns a logger instance. 
     * I suggest you create another abstract on top of this class 
     * to serve all classes in your specific project/sub project.
     */
    abstract protected function logger();
    
    /**
     * Restrict the key to be one type of Redis object
     * @param \Talis\Services\Redis\Client $r
     * @return \Talis\Services\Redis\aClientMask
     */
    abstract protected function get_redis_mask(\Talis\Services\Redis\Client $r):\Talis\Services\Redis\aClientMask;
    
	/**
	 * The namespace part of the key
	 * @return string
	 */
	abstract public function name_space():string;
	
	/**
	 * the name of the entity this key is all about
	 * For example, user cache in launcher module. User is the entity, launcher is the namespace.
	 * @return string
	 */
	abstract public function entity_name():string;
	
	/**
	 * The variable value of this entity.
	 * Key can be empty, in case this is a one thing entity, like a hash.
	 *
	 * @return string
	 */
	public function var_key():string{
	    return $this->var_key;
	}
	
	/**
	 * Returns a debug blurb about this key purpose.
	 * This is also for ease of finding all Redis related objects
	 * @return string
	 */
	abstract public function debug_redis_description():string;
	
	/**
	 * the key of this elelemnt
	 * @return string
	 */
	public function __toString(){
	    return $this->name_space() . self::FIELD_SEPARATOR . $this->entity_name() . self::FIELD_SEPARATOR . $this->var_key();
	}
}

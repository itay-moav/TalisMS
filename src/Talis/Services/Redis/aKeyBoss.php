<?php namespace Talis\Services\Redis;
/**
 * This class is where u manage the key values for each
 * entity we create 
 * 
 * @author itaymoav
 */
abstract class aKeyBoss{
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
    protected function get_client():\Talis\Services\Redis\aClientMask{
        $r = Client::getInstance($this->host(),$this,$this->builder);
        if($this->should_i_serilize()){
            $r->serialize();
        } else {
            $r->dontSerialize();
        }
        return $this->get_redis_mask($r);
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
     * Restrict the key to be one type of Redis object
     * @return \Talis\Services\Redis\aClientMask
     */
    abstract protected function get_redis_mask(Client $r):\Talis\Services\Redis\aClientMask;
    
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
	    return $this->name_space() . self::FIELD_SEPARATOR . $this->sub_entity . self::FIELD_SEPARATOR . $this->var_key();
	}
	
}
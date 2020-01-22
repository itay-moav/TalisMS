<?php namespace Talis\Services\Redis;

abstract class aClientMask{
    /**
     * @var Client
     */
    protected $r;
    
    /**
     * @param Client $r
     */
    public function __construct(Client $r){
        $this->r = $r;
    }
    
    /**
     * Depending on the variable part of the key, suggested to use '*' to initialize a key
     * @return array
     */
    public function keys():array{
        return $this->r->keys();    
    }
    
    public function expire(int $seconds){
        $this->r->expire($seconds);
    }
    
    public function pexpire(int $milliseconds){
        $this->r->pexpire($milliseconds);    
    }
    
    public function del(){
        return $this->r->del();
    }
    
    /**
     * @return int TTL in seconds
     */
    public function ttl():int{
        return $this->r->ttl();
    }
    
    /**
     * @return int TTL in milliseconds,
     */
    public function pttl():int{
        return $this->r->pttl();
    }
    
    /**
     * Redis object type 
     * @return string
     */
    public function type():string{
        return $this->r->type();
    }
    
    /**
     * @return int 1 key exists 0 does not
     */
    public function exists():int{
        return $this->r->exists();
    }
    
}

<?php namespace Talis\Services\Redis\Mask;

trait tSimple{
    /**
     * Restrict the key to be one type of Redis object
     * @return \Talis\Services\Redis\aClientMask
     */
    protected function get_redis_mask(\Talis\Services\Redis\Client $r):\Talis\Services\Redis\aClientMask{
        return new Simple($r);
    }
}

/**
 * Simple one value key space
 * 
 * @author itay
 *
 */
class Simple extends \Talis\Services\Redis\aClientMask{
    /**
     * @return string
     */
    public function get(){
        return $this->r->get();
    }
    
    public function set($value,$ttl=0){
        return $ttl?$this->r->set($value,$ttl):$this->r->set($value);
    }
    
    public function getset($value){
        return $this->r->getset($value);
    }
    
    public function incrby(int $amount){
        return $this->r->incrby($amount);
    }
    
    /**
     * Sets the variable IF not exists
     * @param mixed $value serializable
     */
    public function setnx($value){
        $this->r->setnx($value);
    }
    
    /**
     * Sets value and expiration of key
     * 
     * @param int $seconds
     * @param mixed $value serializable
     */
    public function setex(int $seconds,$value){
        $this->r->psetex($seconds,$value);
    }

    /**
     * Sets value and expiration of key
     * 
     * @param int $milliseconds
     * @param mixed $value serializable
     */
    public function psetex(int $milliseconds,$value){
        $this->r->psetex($milliseconds,$value);
    }
    
    /**
     * @return int length of var
     */
    public function strlen():int{
        return $this->r->strlen();
    }
    
}

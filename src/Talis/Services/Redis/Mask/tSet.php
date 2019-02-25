<?php namespace Talis\Services\Redis\Mask;

trait tSet{
    /**
     * Restrict the key to be one type of Redis object
     * @return \Talis\Services\Redis\aClientMask
     */
    protected function get_redis_mask(\Talis\Services\Redis\Client $r):\Talis\Services\Redis\aClientMask{
        return new Set($r);
    }
}

/**
 * Simple one value key space
 * 
 * @author itay
 *
 */
class Set extends \Talis\Services\Redis\aClientMask implements \Talis\Services\Redis\iScannable{
    /**
     * @param array $members
     * @return number of members added
     */
    public function sadd (array $members):int{
        return \call_user_func_array([$this->r,'sadd'],$members);
    }
    
    /**
     * @return number of elements in set
     */
    public function scard():int{
        return $this->r->scard();
    }
    
    /**
     * @retrurn array of all set members
     */
    public function smembers():array{
        return $this->r->smembers();
    }
    
    /**
     * intersects two other keys and stores them in THIS key
     * 
     * @param \Talis\Services\Redis\aKeyBoss $intersec_this
     * @param \Talis\Services\Redis\aKeyBoss $intersec_with
     * @return int number of elements in current key
     */    
    public function sinterstore(\Talis\Services\Redis\aKeyBoss $intersec_this,\Talis\Services\Redis\aKeyBoss $intersec_with):int{
        return $this->r->sinterstore($intersec_this,$intersec_with);
    }
    
    /**
     * Union input keys and stores them in THIS key
     * 
     * @param array of \Talis\Services\Redis\aKeyBoss
     * @return int number of members in new key
     */
    public function sunionstore(array $keys):int{
        return call_user_func_array([$this->r,'sunionstore'],$keys);
    }
    
    /**
     * Subtracts two keys and stores them in THIS key
     * 
     * @param \Talis\Services\Redis\aKeyBoss $subtract_from_that
     * @param \Talis\Services\Redis\aKeyBoss $subtract_this
     * @return int number of members in THIS key
     */
    public function sdiffstore(\Talis\Services\Redis\aKeyBoss $subtract_from_that, \Talis\Services\Redis\aKeyBoss $subtract_this):int{
        return $this->r->sdiffstore($subtract_from_that,$subtract_this);
    }
    
    /**
     * raw SET scanner
     * 
     * @param int $cursor should be mnanaged by Redis. See iterator function below to see usage
     * @return bool|array
     */
    public function sscan(?int &$cursor,$pattern=false){
        return $this->r->sscan($cursor,$pattern);
    }
    
    /**
     * Scan plugged in here
     * 
     * {@inheritDoc}
     * @see \Talis\Services\Redis\iScannable::scan_me()
     */
    public function scan_me(?int &$cursor,$pattern=false){
        return $this->sscan($cursor,$pattern);
    }
}

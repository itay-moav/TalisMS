<?php namespace Talis\Services\Redis;
/**
 * @author itaymoav
 */
class ScanIterator{
    
    public  $fetch_count   = 0;
    
    /**
     * 
     * @var \Talis\Services\Redis\iScannable
     */
    private $key_boss      = NULL;
    
    /**
     * @param \Talis\Services\Redis\iScannable $key_boss
     */
    public function __construct(\Talis\Services\Redis\iScannable $key_boss){
        $this->key_boss = $key_boss;
    }
    
    /**
     * Iterate this
     * @return \Generator
     */
    public function fetchAll(){
        $cursor        = null;
        while(($row = $this->key_boss->scan_me($cursor))!== false) {
            if(count($row) > 0) {
                foreach($row as $value) {
                    yield $value;
                    $this->fetch_count++;
                }
            }
        }
    }
}

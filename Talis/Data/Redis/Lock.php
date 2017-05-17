<?php
/**
 * Class to provide easy API
 * to developers to use Redlock
 * 
 * @author Itay Moav
 * @date Aug 23 2016
 *
 */
abstract class Data_Redis_Lock{
    private static $redlock_instances = [];
    
    static public function wait_for_lock(Redis_LockForProcess $resource){
        $Redloc = isset(self::$redlock_instances[$resource->lock_name])?self::$redlock_instances[$resource->lock_name]:
                                                                        new RedLock($resource->assumed_seconds_to_finish*2)
        ;
        self::$redlock_instances[$resource->lock_name] = $Redloc;
        $lock_identifier = $Redloc->lock($resource->lock_name,$resource->ttl*2);//2 is a random factor to pad in case process takes longer than usual
        if($lock_identifier === false || !is_array($lock_identifier)){
            dbgn('nolock!');
            throw new Exception_TimeOut('Could not aquire lock');
        }
        return $lock_identifier;
    }
    
    /**
     * 
     * @param array $lock_identifier the return value from self::wait_for_lock
     */
    static public function release_lock($lock_identifier){
        $Redloc = isset(self::$redlock_instances[$lock_identifier['resource']])?self::$redlock_instances[$lock_identifier['resource']]:
                                                                                new RedLock
        ;
        $Redloc->unlock($lock_identifier);
    }
}







/**
 * Implementing a simple lock using Redis
 * @author Itay Moav
 * @date Aug 23 2016
 * @origin https://github.com/ronnylt/redlock-php
 *
 */
class RedLock
{
    private $retryDelay;
    // I HAVE A TTL FOR EACH LOCK AQUIRED, THIS VALUE IF YOU WANT THE PROCESS TO FAIL AFTER SEVERAL TRIES, NOT YET NEEDED IN LMS private $retryCount;
    
    /**
     * @var Redis
     */
    private $instance = null;
    
    /**
     * 
     * @param number $retryDelay miliseconds between each retries
     * @param number $retryCount
     */
    function __construct($longest_process_time_in_seconds)
    {
        $this->retryDelay = 10;//10 seconds between each try
        // I HAVE A TTL FOR EACH LOCK AQUIRED, THIS VALUE IF YOU WANT THE PROCESS TO FAIL AFTER SEVERAL TRIES, NOT YET NEEDED IN LMS  $this->retryCount = ceil($longest_process_time_in_seconds/10);
    }
    
    /**
     * @param strng $resource
     * @param integer $ttl seconds the current process thinks it will need (developer puts it)
     * @return multitype:unknown number string |boolean
     */
    public function lock($resource, $ttl)
    {
        $this->init();
        $token = uniqid();
        // I HAVE A TTL FOR EACH LOCK AQUIRED, THIS VALUE IF YOU WANT THE PROCESS TO FAIL AFTER SEVERAL TRIES, NOT YET NEEDED IN LMS $retry = $this->retryCount;
        
        do {
            if ($this->lockInstance($this->instance, $resource, $token, $ttl)) {
                dbgr('STARTLOCK for ' . $resource,$token);
                return [
                    'resource' => $resource,
                    'token'    => $token,
                ];
            } 
            
            sleep($this->retryDelay);
            // I HAVE A TTL FOR EACH LOCK AQUIRED, THIS VALUE IF YOU WANT THE PROCESS TO FAIL AFTER SEVERAL TRIES, NOT YET NEEDED IN LMS $retry--;
        // I HAVE A TTL FOR EACH LOCK AQUIRED, THIS VALUE IF YOU WANT THE PROCESS TO FAIL AFTER SEVERAL TRIES, NOT YET NEEDED IN LMS } while ($retry > 0);
        }while(true);
        return false;
    }
    
    public function unlock(array $lock)
    {
        $this->init();
        $resource = $lock['resource'];
        $token    = $lock['token'];
        $this->unlockInstance($this->instance, $resource, $token);
    }
    
    /**
     * For now, I use directly the environment
     * Later, We should use dependency injection of the configuration here.
     */
    private function init()
    {
        if (empty($this->instance)) {
            $redis = new \Redis();
            $redis->connect(app_env()['database']['redis']['host']);
            $this->instance = $redis;
        }
    }
    
    private function lockInstance(Redis $instance, $resource, $token, $ttl)
    {
        return $instance->set($resource, $token, ['nx', 'ex' => $ttl]);
    }
    
    private function unlockInstance(Redis $instance, $resource, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $instance->eval($script, [$resource, $token], 1);
    }
}
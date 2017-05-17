<?php
/**
 * To run this test:
 * 1. Open three terminals
 * 2. From each terminal call this file from cli with an  argument either 1, 2 or 3. 
 * 3. The file will count from the given arg[00] to arg[00]+10, sleep a second between each number and write it to a variable in redis.
 * 4. If the test is right, you should see three sequences and not a mess, as each process will run in line.
 * 5. To verify this is a good test, comment both lines of the lock and see you do get a mess in redis
 * 6. use redis-cli monitor to see action
 *  
 * @var
 */
define('LOGFILE','TEST_REDIS_LOCK_');
include '/lms2/production/anahita/bin/init/bootstrap.php';
sleep(10);
$lock_identifier = Data_Redis_Lock::wait_for_lock(Redis_LockForProcess::Test());
$redis = new \Redis();
$redis->connect(app_env()['database']['redis']['host']);
foreach(range($argv[1]*10,$argv[1]*10+10) as $v) {
    $redis->set('TestLock',$v);
    echo "{$v}\n";
    sleep(2);
}
Data_Redis_Lock::release_lock($lock_identifier);

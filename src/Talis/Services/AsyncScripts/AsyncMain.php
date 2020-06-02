#!/bin/php
<?php
/**
 * INSTALL:
 * Put this script in the bin/async folder of your project and name it master_asyncus and give it 755
 * See there has to be a folder stracture of process_category/process_name.php to handle these requests
 */
$this_fl = dirname(__FILE__);
$request = $argv[1];
define('LOGFILE','ASYNC_' . str_replace('/', '_', strtoupper($request)) . '_');
require_once $this_fl . '/../../config/bootstrap.php';

try{
    require_once "{$this_fl}/{$request}.php";
    $Process = new Main(isset($argv[2])?$argv[2]:'');
    $Process->execute();
    $Process->conclusion();
}catch (Exception $e){
    fatal($e);
    exit -1;
}

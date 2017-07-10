<?php
require_once __DIR__ . '/../../../config/environment/'.lifeCycle().'.php';
//require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
function autoload_tests($class) {
    $file_path = str_replace(['_','\\'],'/',$class) . '.php';
    //print_r($file_path . "\n");
    try{
        @include_once $file_path;
    } catch (Exception $e){
        echo "Failed to include {$class}";
    }
}
spl_autoload_register('autoload_tests');
ini_set('include_path', '.' .
    PATH_SEPARATOR . '../../'
);

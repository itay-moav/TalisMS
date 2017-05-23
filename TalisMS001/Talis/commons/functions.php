<?php namespace Talis\commons;

/**
 * functions.php
 * Contains certain low-level functions used throughout the LMS.
 */

/**
 * In order to support lazy loading of classes within PHP, an autoload �magic� function is defined and installed in
 * PHP's execution stack.
 *
 * @param String $class
 * @throws Exception_ClassNotFound If the file does not exist or the class was not found in the file.
 * @return void
 */
function autoload($class) {
	$file_path = str_replace(['_','\\'],'/',$class) . '.php';
	if(!include_once $file_path){
		throw new \Talis\Exception\ClassNotFound("{$file_path} {$class}");
	}
}

//DB shortcuts - since we used it the same all over.

/**
 * READ
 * @return Data_MySQL_DB - READ
 */
function rddb(){
	return Data_MySQL_DB::getInstance(Data_MySQL_DB::READ);
}

/**
 * WRITE
 * @return Data_MySQL_DB - WRITE
 */
function rwdb(){
	return Data_MySQL_DB::getInstance(Data_MySQL_DB::WRITE);
}

/**
 * Check for production environment. Used in the initialize_tests
 */
function isProduction() {
	return strpos(lifecycle(), 'prod') !== false;
}

/**
 * Single point to start async processes from Apache/Cron
 *
 * @param string $request for example report/build will start async/report/build
 * @param array $params
 */
function run_async_proc($request,array $params = []){
    $param_string = '';
    $sep = '';
    foreach($params as $k=>$v){
        $param_string .= $sep . base64_encode($k) . '/' . base64_encode($v);
        $sep = '/';
    }

    $cl = "php -f " . ASYNC_PATH . "/Talis.php {$request} \"{$param_string}\" > /dev/null 2>/dev/null &";
    dbgr('ASYNC',$cl);
    system($cl);
}

/**
 * Cleanup for the above - TODO, r we using it?
 *
 * @param string $str
 * @return mixed
 */
function clean_for_cl($str){
    return str_replace(['-',';','=','`','|','&','*','(',')','^','$','#','@','!','?'],'',$str);
}

/**
 * Takes an array of arrays and recursivly translates to stdClass
 * 
 * @param array $array
 * 
 * @return stdClass
 */
function array_to_object(array $array) {
    $object = new stdClass();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $value = array_to_object($value);
        }
        $object->$key = $value;
    }
    return $object;
}

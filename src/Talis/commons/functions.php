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
 * @throws \Talis\Exception\ClassNotFound If the file does not exist or the class was not found in the file.
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
 * Get a db connection shortcut with config
 * 
 * @return \Talis\Services\Sql\MySqlClient
 */
function mysql_db(string $db_name='mysql'):\Talis\Services\Sql\MySqlClient{
	$config = \app_env()['database'][$db_name]??current(reset(\app_env()['database']));
	return \Talis\Services\Sql\Factory::getConnectionMySQL($db_name,$config);
}

/**
 * Takes an array of arrays and recursivly translates to stdClass
 * 
 * @param array<mixed> $array
 * 
 * @return \stdClass
 */
function array_to_object(array $array) {
    $object = new \stdClass();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $value = array_to_object($value);
        }
        $object->$key = $value;
    }
    return $object;
}

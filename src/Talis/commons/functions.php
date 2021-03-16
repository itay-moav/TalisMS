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

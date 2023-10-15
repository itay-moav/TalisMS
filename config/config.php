<?php namespace init;
ini_set('error_reporting', E_ALL);
ini_set('log_errors',1);
ini_set('display_errors',1);
/**
 * Common config values all subdomains and CLI will be using
 */
//generic paths
\define('CORE_PATH', 		__DIR__ . '/..');
\define('APP_PATH', 		CORE_PATH . '/application');
\define('SHOW_EXCEPTIONS',1);      //DEFINE THIS IF U WANT EXCEPTIONS TO RETURN FULL STACKS TO CLIENT

\ini_set('include_path', '.' .
    PATH_SEPARATOR . APP_PATH .
	PATH_SEPARATOR . CORE_PATH . '/src'
);

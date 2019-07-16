<?php namespace init;
ini_set('error_reporting', E_ALL);
ini_set('log_errors',1);
ini_set('display_errors',1);
/**
 * Common config values all subdomains and CLI will be using
 */
//generic paths
\define('CORE_PATH', 		\app_env()['paths']['root_path']);
\define('APP_PATH', 		CORE_PATH . '/application');
\define('LOG_PATH',			'/var/log/lms2/');

\define('OVERRIDE',          -1);//a way to signal to override calculated values
\define('BACKTRACE_MASK',     0);
\define('FORCE_HTTPS',        1); //a value u send to the url function to force the use of https
\define('DONT_FORCE_SCHEMA',  0); //If it is https it remains https, if it is http it remains http
\define('PREVENT_FORCE_HTTPS',-1); //a value u send to the url function to force the use of http (prevent https)

\ini_set('include_path', '.' .
    PATH_SEPARATOR . APP_PATH .
	PATH_SEPARATOR . CORE_PATH . '/src'
);


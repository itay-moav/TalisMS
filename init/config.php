<?php namespace init;
ini_set('error_reporting', E_ALL|E_STRICT);
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
	PATH_SEPARATOR . '/usr/share/php/TalisMS001'.
    PATH_SEPARATOR . APP_PATH   . '/model' . 
    PATH_SEPARATOR . '/usr/share/php/ZendFW2411'
);


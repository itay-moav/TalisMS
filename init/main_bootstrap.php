<?php
ini_set('error_reporting', E_ALL|E_STRICT);
/**
 * Common config values all subdomains and CLI will be using
 */
//generic paths
define('CORE_PATH', 		'/lms2/production/anahita');
define('ASYNC_PATH',        CORE_PATH . '/bin/async');
define('BATCH_PATH',        CORE_PATH . '/bin/batch');
define('LIB_PATH', 			CORE_PATH . '/lib');
define('FILES_PATH_MASTER',    '/mnt/nfs_files');
define('FILES_PATH_SLAVE',     '/var/www/files');
define('LOG_PATH',			   '/var/log/lms2/');

//NFS master and slave paths
define('UPLOAD_PATH_RELATIVE',   	'uploadassets');
define('CERTIFICATE_PATH_RELATIVE',	'certificateassets');
define('CONTENT_PATH_RELATIVE',     'contentassets');

define('CONTENT_PATH_MASTER',		FILES_PATH_MASTER . '/' . CONTENT_PATH_RELATIVE);
define('COURSE_PATH_MASTER',		FILES_PATH_MASTER . '/courseassets');
define('UPLOAD_PATH_MASTER',		FILES_PATH_MASTER . '/' . UPLOAD_PATH_RELATIVE);
define('USER_PATH_MASTER',			FILES_PATH_MASTER . '/userassets');
define('ORGANIZATION_PATH_MASTER',	FILES_PATH_MASTER . '/orgassets');
define('CERTIFICATE_PATH_MASTER',	FILES_PATH_MASTER . '/' . CERTIFICATE_PATH_RELATIVE);
define('CONTENT_PATH_SLAVE',		FILES_PATH_SLAVE . '/'  . CONTENT_PATH_RELATIVE);
define('COURSE_PATH_SLAVE',		    FILES_PATH_SLAVE . '/courseassets');
define('UPLOAD_PATH_SLAVE',		    FILES_PATH_SLAVE . '/uploadassets');
define('USER_PATH_SLAVE',			FILES_PATH_SLAVE . '/userassets');
define('ORGANIZATION_PATH_SLAVE',	FILES_PATH_SLAVE . '/orgassets');
define('CERTIFICATE_PATH_SLAVE',	FILES_PATH_SLAVE . '/'  . CERTIFICATE_PATH_RELATIVE);

//domain specific paths
define('DOMAIN_PATH', 		CORE_PATH   . '/subdomains/' . SUBDOMAIN);
define('LAYOUT_PATH', 		DOMAIN_PATH . '/layouts');
define('VIEW_PATH', 		DOMAIN_PATH . '/views');

//common values/constants
define('EONFLUX_ORG_ID', 	 1);
define('BIN_LOGIN_ORG_ID',  -1);//an org to use when login a user in the cron system and other backend processes.
define('OVERRIDE',          -1);//a way to signal to override calculated values
define('BACKTRACE_MASK',     0);
define('FORCE_HTTPS',        1); //a value u send to the url function to force the use of https
define('DONT_FORCE_SCHEMA',  0); //If it is https it remains https, if it is http it remains http
define('PREVENT_FORCE_HTTPS',-1); //a value u send to the url function to force the use of http (prevent https)

ini_set('include_path', '.' .
    PATH_SEPARATOR . DOMAIN_PATH        . '/plugins' .
    PATH_SEPARATOR . LIB_PATH           . '/Talis' .
    PATH_SEPARATOR . DOMAIN_PATH        . '/controllers' .
    PATH_SEPARATOR . CORE_PATH          . '/model' .
    PATH_SEPARATOR . '/usr/share/php/ZendFW2411' . 
    PATH_SEPARATOR . '/usr/share/php/Google2010'
);

//some other bootstrapping and important functions and the ENVIRONMENT definitions
include LIB_PATH.'/Talis/commons.php';
include LIB_PATH.'/Talis/commons/url.php';
include CORE_PATH . '/environments/'.lifeCycle().'.php';

//error handling
//TODO See if I need this - ini_set('display_errors', app_env()['debug']);
/**
 * None fatal errors.
 * php ini settings & SPL (TODO move to php.ini)
 */

//ITAY not sure we need this, as we redirect stderr into our own log ini_set('log_errors', true);
set_error_handler('error_handler');
if(!app_env()['debug']){
    function fatal_handler() {
        ini_set('memory_limit', '257M');
        $error_types =[E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_RECOVERABLE_ERROR, E_PARSE];
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], $error_types)) {
            error("\n\nERROR occured!\nFile [{$error['file']}].\nLine: [{$error['line']}].\nMessage: [{$error['message']}]\n\n");
        }
        exit(ERRORCODE::GENERAL);
    }
    
}else{
    function fatal_handler() {
        $error = error_get_last();
        if ($error !== null) {
            echo ("\n\nERROR occured!\nFile [{$error['file']}].  Line: [{$error['line']}].   Message: [{$error['message']}]\n\n");
        }
        exit(ERRORCODE::GENERAL);
    }
}
register_shutdown_function("fatal_handler");
include '/usr/share/php/Dompdf070/dompdf/autoload.inc.php';
spl_autoload_register('autoload');


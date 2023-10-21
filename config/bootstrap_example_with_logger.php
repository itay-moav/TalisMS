<?php

//HEre you put constants and ini settings
require_once __DIR__ . '/config.php';


//HANDLE AUTOLOADING FOR CODE UNDER APPLICATION FOLDER
/**
 *
 * @return callable
 */
function getAutoloader(){
    /**
     * @param string $class
     * @throws \Talis\Exception\ClassNotFound
     */
    return function (string $class):void {
        $file_path = str_replace('\\','/',$class) . '.php';
        require_once $file_path;
    };
}

spl_autoload_register(getAutoloader(),true);


//COMPOSER
require_once __DIR__ . '/../vendor/autoload.php';

//Initializations of some Talis core values.
\Talis\Corwin::$APP_PATH = APP_PATH;

//Shortcut functions for usage of default logger dbg,dbgn,dbgr,info,warning,error,fatal
\ZimLogger\MainZim::include_shortcuts();


//Instantiate the logger. This just sets the main/defaul logger, u can use other instances throughout.
//U can overwrite this one too later on
\ZimLogger\MainZim::setGlobalLogger(
		'talisms_test',
		'Stdio',
		4,
		'/some/path/to/write/log',
		false
);

//TODO Move this to another bootstrap with an example
//\Talis\Corwin::$registered_router = 'Some Router Class Name to override defaults'; 

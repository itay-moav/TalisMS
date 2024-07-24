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

//Instantiate the logger. This just sets the main/defaul logger, u can use other instances throughout.
//U can overwrite this one too later on
\ZimLogger\MainZim::setGlobalLogger(
    'talisms_test',
    'Stdio',
    4,
    '/some/path/to/write/log',
    false
);

//Shortcut functions for usage of default logger dbg,dbgn,dbgr,info,warning,error,fatal
\ZimLogger\MainZim::include_shortcuts();

// necessary for including the API classes, for example (in Corwin)
\Talis\Corwin::$APP_PATH = APP_PATH;

//Sets the logger used inside Talis, if need a separate logger just for Talis lib errors, this is where u overwrite it
\Talis\Corwin::set_logger(new ZimLoggerWrapper(\ZimLogger\MainZim::$GlobalLogger));

//TODO Move this to another bootstrap with an example
//\Talis\Corwin::$registered_router = 'Some Router Class Name to override defaults'; 



class ZimLoggerWrapper implements \Talis\commons\iLogger{
    
    /**
     * 
     */
    public function __construct(private \ZimLogger\Handlers\aLogHandler $logger){

    }

    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     */
    public function debug(mixed $inp):void{
        $this->logger->debug($inp);
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function info(mixed $inp,bool $full_stack):void{
        $this->logger->debug($inp,$full_stack);
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function warning(mixed $inp,bool $full_stack):void{
        $this->logger->debug($inp,$full_stack);
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function error(mixed $inp,bool $full_stack):void{
        $this->logger->debug($inp,$full_stack);
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function fatal(mixed $inp,bool $full_stack):void{
        $this->logger->debug($inp,$full_stack);
    }   
}
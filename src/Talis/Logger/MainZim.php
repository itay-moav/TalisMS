<?php namespace Talis\Logger;
/**
 * Logger API as a procedural, because procedural is less writing.
 * and less writing is good.
 * I ASSUME YOU INTIALIZED THE LOGGER IN YOUR BOOTSTRAP, AS ONE OF THE FIRST THINGS U DO IN YOUR APP!
 */
 
function debug($inp){
    is_object(MainZim::$CurrentLogger)?MainZim::$CurrentLogger->debug($inp):'';
	return null;
}

function info($inp,$full_stack=false){
	is_object(MainZim::$CurrentLogger)?MainZim::$CurrentLogger->info($inp,$full_stack):'';
	return null;
}

function warning($inp,$full_stack=false){
	is_object(MainZim::$CurrentLogger)?MainZim::$CurrentLogger->warning($inp,$full_stack):'';
	return null;
}

function error($inp,$full_stack=false){
	is_object(MainZim::$CurrentLogger)?MainZim::$CurrentLogger->error($inp,$full_stack):'';
	return null;
}

function fatal($inp,$full_stack=false){
	is_object(MainZim::$CurrentLogger)?MainZim::$CurrentLogger->fatal($inp,$full_stack):'';
	return null;
}
 
/**
 * for debug purposes only. Echoes stuff to the slog
 *
 * @param mixed $var, preferably an array you want to log
 * @param boolean $die should I die after sloging?
 */
function dbg($var,$die=false){
	debug($var);
	if($die){
		die;
	}
}
function dbgd($var){
	dbg($var,true);
}
function dbgn($n,$die=false){
	dbg("==========================={$n}===============================", $die);
}
function dbgt($var = null, $die = false) {
	$E = new \Exception();
	dbg($E->getTraceAsString());
	dbg($var, $die);
}
function dbgh($n,$var,$die = false){
	dbgn($n);
	dbg($var,$die);
}
function dbgr($n,$var){
	dbgn($n);
	dbg($var);
}


/**
 * Logger Factory and manager, handles creating logs for systems and holding API for the current log.
 */
abstract class MainZim{
    
    /**
     * Call this to includes this file with autoloader.
     * Used inside this lib where a Bootstrap not always exists
     * to instantiate the logger
     */
    static public function include(){}
	
	/**
	 * @var \Talis\Logger\MainZim current Logger to be used in the app.
	 *             To change current Logger, simply use the factory again (or just instantiate 
	 *             the logger you want.
	 */
	static public $CurrentLogger = null;
	
	/**
	 * Sets the global logger as a static member in MainZim, it is the one who will be accessible from the dbg() functions 
	 * 
	 * @param string $log_name		A name for the log output (for example, if this is a file log, this would be part of the file name, usage
	 *								depends on the specific Logger class used.
	 * @param string $logger_classname	Logger type, depends on the class names u have under the Logger folder. Use the Logger_[USE_THIS] value
	 * 								as the available types.
	 * @param integer $verbosity_level which type of messages do I actually log, Values are to use the constants Logger::VERBOSITY_LVL_*
	 *								Sadly, in your environment file, you will probably need to use pure numbers, unless u include the Logger.php 
	 *								before you load the environment values (where you should configure the system verbosity level).
	 * @param mixed $target_stream	The target of the Logger, can be any class implementing the Logger_iWrite interface
	 *								that wraps a resource (like a socket/DB connection etc.), File path if writes to file or nothing, is simply Echo's  
	 *             To change current Logger, simply use the factory again (or just instantiate 
	 *             the logger you want.
	 * @param bool $use_low_memory_footprint This flag will prevent from a full dump of an object, as there might be huge objects which can cause out of memory errors.
	 *                                       Flag can also be used differently in each concrete logger 
	 *
	 * @return \Talis\Logger\MainZim
	 */
	static public function setGlobalLogger(string $log_name,string $logger_classname,int $verbosity_level,$target_stream=null,bool $use_low_memory_footprint=false):Streams\aLogStream{
		return self::$CurrentLogger = self::factory($log_name,$logger_classname,$verbosity_level,$target_stream,$use_low_memory_footprint);
	}
	
	/**
	 * Creates a logger
	 * 
	 * @param string $log_name		A name for the log output (for example, if this is a file log, this would be part of the file name, usage
	 *								depends on the specific Logger class used.
	 * @param string $logger_classname	Logger type, depends on the class names u have under the Logger folder. Use the Logger_[USE_THIS] value
	 * 								as the available types.
	 * @param integer $verbosity_level which type of messages do I actually log, Values are to use the constants Logger::VERBOSITY_LVL_*
	 *								Sadly, in your environment file, you will probably need to use pure numbers, unless u include the Logger.php
	 *								before you load the environment values (where you should configure the system verbosity level).
	 * @param mixed $target_stream	The target of the Logger, can be any class implementing the Logger_iWrite interface
	 *								that wraps a resource (like a socket/DB connection etc.), File path if writes to file or nothing, is simply Echo's
	 *             To change current Logger, simply use the factory again (or just instantiate
	 *             the logger you want.
	 * @param bool $use_low_memory_footprint This flag will prevent from a full dump of an object, as there might be huge objects which can cause out of memory errors.
	 *                                       Flag can also be used differently in each concrete logger
	 *
	 * @return \Talis\Logger\MainZim
	 */
	static public function factory(string $log_name,string $logger_classname,int $verbosity_level,$target_stream=null,bool $use_low_memory_footprint=false):Streams\aLogStream{
	    $class_name = strpos($logger_classname, '_')? ('\\' . $logger_classname) : ('\Talis\Logger\Streams\\' . ucfirst($logger_classname));
	    return new $class_name($log_name,$verbosity_level,$target_stream,$use_low_memory_footprint);
	}
	
	/**
	 * Switches the current logger to use a low memory foot print.
	 * until this becomes an issue, this value will not pass on if current logger is switched inside the same session.
	 */
	static public function currentLoggerUseLowMemoryFootprint(){
		self::$CurrentLogger->setUseLowMemoryFootprint(true);
	}
}
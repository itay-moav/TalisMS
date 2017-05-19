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
	$E = new Exception();
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
	 * @var Logger_MainZim current Logger to be used in the app.
	 *             To change current Logger, simply use the factory again (or just instantiate 
	 *             the logger you want.
	 */
	static public $CurrentLogger = null;
	
	/**
	 * @param string $log_name		A name for the log output (for example, if this is a file log, this would be part of the file name, usage
	 *								depends on the specific Logger class used.
	 * @param string $logger_type	Logger type, depends on the class names u have under the Logger folder. Use the Logger_[USE_THIS] value
	 * 								as the available types.
	 * @param integer $verbosity_level which type of messages do I actually log, Values are to use the constants Logger::VERBOSITY_LVL_*
	 *								Sadly, in your environment file, you will probably need to use pure numbers, unless u include the Logger.php 
	 *								before you load the environment values (where you should configure the system verbosity level).
	 * @param mixed $target_stream	The target of the Logger, can be any class implementing the Logger_iWrite interface
	 *								that wraps a resource (like a socket/DB connection etc.), File path if writes to file or nothing, is simply Echo's  
	 *             To change current Logger, simply use the factory again (or just instantiate 
	 *             the logger you want.
	 *
	 * @return Logger_MainZim
	 */
	static public function factory($log_name,$logger_type,$verbosity_level,$target_stream=null){
		$class_name = '\Talis\Logger\Streams\\' . ucfirst($logger_type);
		//echo $class_name;die;
		self::$CurrentLogger = new $class_name($log_name,$verbosity_level,$target_stream);
		return self::$CurrentLogger;
	}
}
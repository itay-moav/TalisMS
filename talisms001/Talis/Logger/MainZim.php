<?php
/**
 * Logger API as a procedural, because procedural is less writing.
 * and less writing is good.
 * I ASSUME YOU INTIALIZED THE LOGGER IN YOUR BOOTSTRAP, AS ONE OF THE FIRST THINGS U DO IN YOUR APP!
 */
 
function debug($inp){
    is_object(Logger_MainZim::$CurrentLogger)?Logger_MainZim::$CurrentLogger->debug($inp):'';
	return null;
}

function info($inp,$full_stack=false){
	is_object(Logger_MainZim::$CurrentLogger)?Logger_MainZim::$CurrentLogger->info($inp,$full_stack):'';
	return null;
}

function warning($inp,$full_stack=false){
	is_object(Logger_MainZim::$CurrentLogger)?Logger_MainZim::$CurrentLogger->warning($inp,$full_stack):'';
	return null;
}

function error($inp,$full_stack=false){
	is_object(Logger_MainZim::$CurrentLogger)?Logger_MainZim::$CurrentLogger->error($inp,$full_stack):'';
	return null;
}

function fatal($inp,$full_stack=false){
	is_object(Logger_MainZim::$CurrentLogger)?Logger_MainZim::$CurrentLogger->fatal($inp,$full_stack):'';
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
 * Logger abstract, handles creating logs for systems.
 */
abstract class Logger_MainZim{
	const	VERBOSITY_LVL_DEBUG		= 4,
			VERBOSITY_LVL_INFO		= 3,
			VERBOSITY_LVL_WARNING	= 2,
			VERBOSITY_LVL_ERROR		= 1,
			VERBOSITY_LVL_FATAL		= 0
	;
	
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
		$class_name = 'Logger_Streams_' . ucfirst($logger_type);
		self::$CurrentLogger = new $class_name($log_name,$verbosity_level,$target_stream);
		return self::$CurrentLogger;
	}
	
	/**
	 * This function writes to the designated output stream/resources.
	 */
	abstract protected function log($txt,$severity,$full_stack_data=null);
	
	/**
	 * Translate to string the input, how to output? that depends on how
	 * you implemented the `log` method
	 * 
	 * @param unknown $inp
	 * @param unknown $severity
	 * @param string $full_stack
	 */
	protected function tlog($inp,$severity,$full_stack=false){
		if ($inp === null){
			$inp = 'NULL';
			
		}elseif($inp instanceof Exception){
            //do nothing
            		     
		}elseif(!is_string($inp)){
			$inp = print_r($inp,true);
		}
		
		$full_stack_data = null;
		if($full_stack){
		    $full_stack_data['session'] = isset($_SESSION)?$_SESSION:[];
		    $full_stack_data['request'] = isset($_REQUEST)?$_REQUEST:[];
		    $full_stack_data['server']  = isset($_SERVER)?$_SERVER:[];
		    $full_stack_data['database'] = Data_MySQL_DB::getDebugData();
		}
		$this->log($inp,$severity,$full_stack_data);
	}
	
	protected	$log_name			= '',
				$verbosity_level	= Logger_MainZim::VERBOSITY_LVL_DEBUG,
				$target_stream		= null
	;
	
	/**
	 * @param string $log_name
	 * @param enum $verbosity_level
	 * @param string $target_stream
	 */
	final public function __construct($log_name,$verbosity_level,$target_stream=null){
		$this->log_name = $log_name;
		$this->verbosity_level = $verbosity_level;
		$this->target_stream = $target_stream;
		$this->init();
	}
	
	protected function init(){
		return $this;	
	}
	
	public function debug($inp){
		if($this->verbosity_level >= self::VERBOSITY_LVL_DEBUG){
			$this->tlog($inp,self::VERBOSITY_LVL_DEBUG);
		}
	}
	
	public function info($inp,$full_stack){
		if($this->verbosity_level >= self::VERBOSITY_LVL_INFO){
			$this->tlog($inp,self::VERBOSITY_LVL_INFO,$full_stack);
		}
	}

	public function warning($inp,$full_stack){
		if($this->verbosity_level >= self::VERBOSITY_LVL_WARNING){
			$this->tlog($inp,self::VERBOSITY_LVL_WARNING,$full_stack);
		}
	}
	
	public function error($inp,$full_stack){
		if($this->verbosity_level >= self::VERBOSITY_LVL_ERROR){
			$this->tlog($inp,self::VERBOSITY_LVL_ERROR,$full_stack);
		}
	}
	
	public function fatal($inp,$full_stack){
		if($this->verbosity_level >= self::VERBOSITY_LVL_FATAL){
			$this->tlog($inp,self::VERBOSITY_LVL_FATAL,$full_stack);
		}
	}
}
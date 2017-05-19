<?php namespace Talis\Logger\Streams;
abstract class AbstractStream{
	protected const	VERBOSITY_LVL_DEBUG		= 4,
					VERBOSITY_LVL_INFO		= 3,
					VERBOSITY_LVL_WARNING	= 2,
					VERBOSITY_LVL_ERROR		= 1,
					VERBOSITY_LVL_FATAL		= 0
	;
	
	protected $log_name = '', 
			  $verbosity_level = null, 
	          $target_stream = null
	;
	
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

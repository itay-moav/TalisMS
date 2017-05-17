<?php
class Logger_Streams_File extends Logger_MainZim{
	protected function init(){
		$this->log_name = $this->target_stream . $this->log_name . @date('m_d_Y', time()).'.log';
		return $this;	
	}
	
	protected function log($inp,$severity,$full_stack_data = null){
		try {
			$stream = fopen($this->log_name, 'a');
			fwrite($stream, "[{$severity}][".@date('h:i:s', time())."] ".$inp.PHP_EOL);
			if($full_stack_data){
			    fwrite($stream, "[FULL STACK] \n" . print_r($full_stack_data,true) . PHP_EOL);
			}
			fclose($stream);
		} 
		catch (Exception $e)
		{
			throw new Exception('Unable to open log file.');
		}
	}
}

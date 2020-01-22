<?php namespace Talis\Logger\Streams;
/**
 * Stream that will create one file per session, and add to header of each log entry the request identifier
 * Even if u do same request twice, it will have different uid
 * 
 * @author Itaty Moav
 */
class Logger_Streams_FileSessionReq extends aLogStream{
    /**
     * @var string request uri '/'=>'_' + a rand(1,100) to prevent confusing the same request for the same user twice, as one request
     */
    private $request_identifier;
    
    /**
     * @var resource file handler
     */
    private $stream;
    
	protected function init(){
	    //get the request + rand number ( same user same requests at the same time). All rquests go into the same file.
	    $this->request_identifier = ( (isset($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:join('__',$_SERVER['argv']) ) . '__rnd' . rand(1,100);
	    
	    /// calc the log name
		$this->log_name = $this->target_stream . $this->log_name . @date('m_d_Y', time()). '_sess' . Data_Session::getSessId() . '.log';
        
		//If this does not work, I do not want to catch the error, I want system to crash
	    $this->stream = fopen($this->log_name, 'a'); 
		return $this;
	}
	
	protected function log($inp,$severity,$full_stack_data = null){
		fwrite($this->stream, PHP_EOL . "[{$severity}][".@date('h:i:s', time())."] [{$this->request_identifier}]" . PHP_EOL . $inp . PHP_EOL);
		if($full_stack_data){
		    fwrite($this->stream, "[FULL STACK] \n" . print_r($full_stack_data,true) . PHP_EOL);
		}
	}
	
	/**
     * release resources.
	 */
	public function __destruct(){
        fclose($this->stream);   
	}
}

<?php
if(!defined('OVERRIDE')) define('OVERRIDE',-1);
if(!defined('BACKTRACE_MASK')) define('BACKTRACE_MASK',0);
/**
 * Use this class to send email for log (using internal email not google)
 * 
 * @author Naghmeh
 */
class Logger_Streams_LmsEmail extends Logger_MainZim{
  
	protected function log($inp,$severity,$full_stack_data = null){
	    //making sure nothing triggers this logger from here (NO TO recursion!)
	    self::$CurrentLogger = new Logger_Streams_Nan('nan',self::VERBOSITY_LVL_FATAL);
	    if($inp instanceof Exception){
		    $bctr = $inp->getTraceAsString();
	    } else {
		    $bctr = debug_backtrace(BACKTRACE_MASK);
		    $bctr = 'odedejoy' . print_r(array_slice($bctr,4),true);
        }
        $data = [
            'severity'=>$severity,
            'exception_message' => ($inp instanceof Exception)?$inp->getMessage():$inp,
            'exception_trace' => $bctr,
            'request' => $full_stack_data? print_r($full_stack_data['request'],true) . ' XXXXXXX ' . file_get_contents('php://input'):'',
            'session' => $full_stack_data? print_r($full_stack_data['session'],true):'',
            'server'  => $full_stack_data? print_r($full_stack_data['server'],true):'',
            'queries' => $full_stack_data? print_r($full_stack_data['database'],true):'',
        ];

		//EMAIL
		try{
		    
		    $subject = '[' . lifeCycle() . '] ' . $data['exception_message'];
		    $body ='<br> Severity: '        .$data['severity']             .'<br>'.
		  		   '<br> Exception_message: '.$data['exception_message']   .'<br>'.
		  		   '<br> Exception_trace: ' .$data['exception_trace']      .'<br>'.
		  		   '<br> Request: '         .$data['request']              .'<br>'.
		  		   '<br> Session: '         .$data['session']              .'<br>'.
		  		   '<br> Server: '          .$data['server']               .'<br>'.
		  		   '<br> Queries: '          .$data['queries']             .'<br>';
		    (new CommCenter_Email_SenderDebug(new CommCenter_Email_Message(0,1,[OVERRIDE],$subject,$body)))->send();
		    
		}catch (Exception $e){
		    //This is a log just in case of a total crash!
		    $c = app_env();
		    $EmergencyLog = new Logger_Streams_File('BLACK_LOG_IS_DOWN_',self::VERBOSITY_LVL_FATAL,$c['log']['uri']);
		    
		    //WRITE TO LOGFILE, THIS IS LAST RESORT, as might be emails or Data base has failed
		    $EmergencyLog->error($e,true);
		}finally {
		    self::$CurrentLogger = $this;
		}
	}
}

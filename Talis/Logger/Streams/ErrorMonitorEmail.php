<?php
/**
 * Use of tabl error_monitor + emails on info | warning | error | fatal
 * Will always use full stack for warning | error | fatal
 * 
 * @author itaymoav
 */
class Logger_Streams_ErrorMonitorEmail extends Logger_MainZim{
    public function warning($inp,$full_stack){
        parent::warning($inp,true);
    }
    
    public function error($inp,$full_stack){
        parent::error($inp,true);
    }
    
    public function fatal($inp,$full_stack){
        parent::fatal($inp,true);
    }
    	
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
            'exception_trace' => $bctr,//($inp instanceof Exception)?print_r($inp->getTraceAsString(),true): 'odedejoy' . print_r(debug_backtrace(BACKTRACE_MASK),true),
            'request' => $full_stack_data? print_r($full_stack_data['request'],true) . ' XXXXXXX ' . file_get_contents('php://input'):'',
            'session' => $full_stack_data? print_r($full_stack_data['session'],true):'',
            'server'  => $full_stack_data? print_r($full_stack_data['server'],true):'',
            'queries' => $full_stack_data? print_r($full_stack_data['database'],true):'',
        ];

        //DB
	    try {//write to DB
	        IDUHub_Lms2prod_ErrorMonitoring::createRecord($data);
	        
		}catch (Exception $e){//making sure db crashes won't kill the email thingy
		    //This is a log just in case of a total crash!
		    $c = app_env();
		    $EmergencyLog = new Logger_Streams_File('BLACK_LOG_IS_DOWN_',self::VERBOSITY_LVL_FATAL,$c['log']['uri']);
		    
		    //Add DB FAILED to the message
		    $data['exception_message'] = 'NOTICE DB FAILED == ' . $data['exception_message'];
		    $EmergencyLog->fatal($e,true);
		}
		
		//EMAIL
		try{
		    run_async_proc('email/sendlog',[
		                          'subject'   => '[' . lifeCycle() . '] ' . $data['exception_message'],
		                          'body'      => $data['exception_trace']
            ]);
		}catch (Exception $e){
		    //This is a log just in case of a total crash!
		    $c = app_env();
		    $EmergencyLog = new Logger_Streams_File('BLACK_LOG_IS_DOWN_',self::VERBOSITY_LVL_FATAL,$c['log']['uri']);
		    
		    //WRITE TO LOGFILE, THIS IS LAST RESORT, as might be emails or Data base has failed
		    $EmergencyLog->fatal($e,true);
		}finally {
		    self::$CurrentLogger = $this;
		}
	}
}

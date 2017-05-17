<?php
class Logger_Streams_ColoredFile extends Logger_MainZim{
    const   VI_COLOR__RED   = 9,
            VI_COLOR__BLUE  = 12,
            VI_COLOR__GREEN = 2,
            VI_COLOR__WHITE = 14,
            VI_COLOR__GRAY  = 7
    ;
    
    static $sql_keywords = ['/INSERT INTO/', 
                            '/UPDATE/',
                            '/VALUES/',
                            '/UNION/',
                            '/SELECT/',
                            '/SQL_CALC_FOUND_ROWS/',
                            '/AND/',
                            '/FROM/',
                            '/OR\s/',
                            '/WHERE/',
                            '/LIMIT/',
                            '/OFFSET/',
                            '/JOIN/',
                            '/GROUP BY/',
                            '/LEFT/',
                            '/ON\s/',
                            '/\sAS\s/',
                            '/ IN /',
                            '/DISTINCT/',
                            '/ORDER BY/',
                            '/SET/',
                            '/ ASC/',
                            '/DUPLICATE KEY/',
                            '/BETWEEN/',
                            '/UNIX_TIMESTAMP/',
                            '/FROM_UNIXTIME/',
                            '/COUNT/',
                            '/ROUND/',
                            '/FOUND_ROWS/',
                            '/RAND/',
                            '/GROUP_CONCAT/',
                            '/CONCAT/',
                            '/NOW/',
                            '/TIME/'];
    
	protected function init(){
		$this->log_name = $this->target_stream . $this->log_name . @date('m_d_Y', time()).'.log';
		return $this;	
	}
	
	protected function log($inp,$severity,$full_stack_data = null){
	    //TODO move to a formatter later.
	    $inp = str_replace("\t","    ",$inp);
	    if($severity == self::VERBOSITY_LVL_DEBUG){
	        $severity_out = self::colorize($severity,self::VI_COLOR__GREEN);
	        $inp = preg_replace(self::$sql_keywords,self::colorize("$0",self::VI_COLOR__WHITE),$inp);// var_dump($inp);
	    }else{
	        $severity_out = self::colorize($severity,self::VI_COLOR__RED);
	    }
	    	
	    if($severity < self::VERBOSITY_LVL_INFO){
	        $inp = self::colorize($inp,self::VI_COLOR__RED);
	    }
	    //EOF TODO move to formatter 
	    
	    try {
			$stream = fopen($this->log_name, 'a');
			fwrite($stream, "[{$severity_out}][" . self::colorize(@date('h:i:s', time()),self::VI_COLOR__GRAY) . "] " . $inp . PHP_EOL);
			
			if($full_stack_data){
			    fwrite($stream, "[FULL STACK] \n" . print_r($full_stack_data,true) . PHP_EOL);
			}
			fclose($stream);
		}catch (Exception $e){
			throw new Exception('Unable to open log file.');
		}
	}
	
	static private function colorize($txt,$color){
	    return "\033[38;5;{$color}m{$txt}\033[0m";
	}
}

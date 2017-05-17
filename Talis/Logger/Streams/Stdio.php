<?php
class Logger_Streams_Stdio extends Logger_MainZim{
	protected function log($inp,$severity,$full_stack_data = null){
		echo $inp . "\n";
		if($full_stack_data){
		    echo "=============================== FULL STACK ======================================\n";
		    print_r($full_stack_data);
		}
		
	}
}
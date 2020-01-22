<?php namespace Talis\Logger\Streams;
/**
 * run redis-cli monitor | php ./redis_format.php | tee baba.log
 * @author Itay Moav
 *
 */
class Redis extends aLogStream{
	protected function log($inp,$severity,$full_stack_data = null){
    	$config = app_env();
        $host  = $config['database']['redis']['host'];
        try{
        	$R = new Redis;
        	$R->connect($host);
        	$R->set('REDIS_MONITOR',$inp);
        	if($full_stack_data){
        	    $R->set('REDIS_MONITOR',"=============================== FULL STACK ======================================");
        	    $R->set('REDIS_MONITOR',print_r($full_stack_data,true));
        	}

        }catch(\Exception $E){
    		echo "REDIS DEAD!\n";
    		echo $E->getMessage();
    		die;
        }
	}
}


/*====redis_formatter
 while ($line = fgets(STDIN)) {
    $line = explode('"REDIS_MONITOR" "',$line)[1];
    $line = substr($line,0,-2)."\n";
    $line = str_replace(['\n','\t'],["\n","\t"],$line);
     fputs(STDOUT,$line);
}
 
 
 */
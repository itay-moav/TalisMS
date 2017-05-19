<?php
function app_env(){
	$ret = [
	        'debug' =>  true,
			'debug_verbosity' => 2, //[1] no debug trace, [2] debug trace up to 4 lines, no args, [3] debug trace up to 4 lines + args , [4] full debug trace 
			'log' =>[
				'name'	    => 'TALISMS_SCHEDULER_',
				'handler'	=> 'File',//'FileSessionReq',//'Redis',//ColoredFile',//'ErrorMonitorEmail',//'Stdio',//'Nan'
				'verbosity' => 4,
				'uri'		=> '/var/log/lms2/'
			],
	
			'paths'=> [
				'root'		=> '/home/admin/dev/talisms',
			    'base_url'  => '192.168.12.148/api/scheduler'
			],
			
			'microservices' => [
				'mail'	    => [
							'url'  	=> '192.168.12.148/api/mail',
						    'async' => true]
			],
			
			'database'=>[
				'unify_read_write' => true,
                'master'=>array(
			        'host'=> '127.0.0.1',
			        'database'=>'lms3course',
			        'username'=>'root',
			        'password'=>'123456!!',
			        'verbosity'=>2
			    ),
			    'slave'=>array(
			        'host'=> '127.0.0.1',
			        'database'=>'lms3course',
			        'username'=>'root',
			        'password'=>'123456!!',
			        'verbosity'=>2
			    ),
			    'SOLR' => array(
					'slave'	=> 'http://localhost:8080/solr/',
					'master'=> 'http://localhost:8080/solr/'
				),
				'redis'=>array(
					'host'=>'localhost',
					'verbosity'=>1 //0 - no out put; 1- shows the query 2- shows the query + results
				),
			    'activeMQ' => [
			        'port'         => '61613',
			        'host'         => 'localhost',
			        'timeout_sec'  => 2,//Activemq will wait for read activity on a socket before returning no messages.
			        'timeout_usec' => 0
			    ]
			]
			
    ];
	
	return $ret;
}
	
	

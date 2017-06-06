<?php
require_once __DIR__ . '/../../config/environment/'.lifeCycle().'.php';

ini_set('include_path', '.' .
		PATH_SEPARATOR . '/usr/share/php/TalisMS001' .
		PATH_SEPARATOR . '/usr/share/php/ZendFW2411'
);

function autoload($class) {
	$file_path = str_replace(['_','\\'],'/',$class) . '.php';
	include_once $file_path;
}

spl_autoload_register('autoload');

//Logger
\Talis\Logger\MainZim::factory(
		'none important',
		'Stdio',
		4,
		'none important'
);

class talis extends Talis\Services\ActiveMQ\Publisher{
	use \Talis\Services\ActiveMQ\tQueue;
}
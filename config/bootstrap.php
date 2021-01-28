<?php 
require_once __DIR__ . '/config.php';
//some other bootstrapping and important functions and the ENVIRONMENT definitions
require_once 'Talis/commons/functions.php';
spl_autoload_register('Talis\commons\autoload');

//Logger
\ZimLogger\MainZim::setGlobalLogger(
		'talisms_test',
		'Stdio',
		4,
		'/some/path/to/write/log',
		false
);

require_once 'Talis/Logger/shortcuts.php';

//\Talis\Corwin::$registered_router = 'Some Router Class Name to voverride defaults'; 

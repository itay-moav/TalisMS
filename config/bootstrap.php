<?php 
require_once app_env()['paths']['root_path']. '/config/config.php';
//some other bootstrapping and important functions and the ENVIRONMENT definitions
require_once 'Talis/commons/functions.php';
spl_autoload_register('Talis\commons\autoload');

//Logger
Talis\Logger\MainZim::setGlobalLogger(
		app_env()['log']['name'],
		app_env()['log']['handler'],
		app_env()['log']['verbosity'],
		app_env()['log']['uri'],
		app_env()['log']['low_memory_footprint']
);

require_once 'Talis/Logger/shortcuts.php';

//\Talis\Corwin::$registered_router = 'Some Router Class Name to voverride defaults'; 
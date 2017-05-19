<?php
require_once app_env()['paths']['root']. '/init/config.php';
//some other bootstrapping and important functions and the ENVIRONMENT definitions
include TALIS_PATH . '/commons/functions.php';
spl_autoload_register('commons\autoload');

//Logger
Logger_MainZim::factory(
		app_env()['log']['name'],
		app_env()['log']['handler'],
		app_env()['log']['verbosity'],
		app_env()['log']['uri']
);


require_once CORE_PATH . '/init/Talis.php';
(new init\Talis)->gogogo();

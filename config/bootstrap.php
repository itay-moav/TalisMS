<?php 
require_once app_env()['paths']['root_path']. '/config/config.php';
//some other bootstrapping and important functions and the ENVIRONMENT definitions
require_once 'Talis/commons/functions.php';
spl_autoload_register('Talis\commons\autoload');

//Logger
Talis\Logger\MainZim::factory(
		app_env()['log']['name'],
		app_env()['log']['handler'],
		app_env()['log']['verbosity'],
		app_env()['log']['uri']
);


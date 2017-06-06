#!/bin/php
<?php
require_once __DIR__ . '/../../config/environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
(new \Talis\Main\Cli)->gogogo($argv[1],$argv[2],$argv[3]??false);
exit(0);
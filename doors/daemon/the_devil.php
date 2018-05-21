#!/bin/php
<?php
require_once __DIR__ . '/../../config/environment/you_need_to_have_a_file_here.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
(new \Talis\Doors\Daemon)->gogogo($argv[1]);
exit(0);

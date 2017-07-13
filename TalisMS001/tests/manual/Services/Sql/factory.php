<?php
require_once '../../../../../config/environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
$c = Talis\Services\Sql\Factory::getConnectionMySQL('testconn',[]);


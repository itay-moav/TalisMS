<?php
require_once __DIR__ . '/../../config/environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
(new \Talis\Doors\HTTP)->gogogo(app_env()['paths']['root_uri']);
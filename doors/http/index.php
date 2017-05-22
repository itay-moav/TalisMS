<?php
require_once __DIR__ . '/../../config/environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
//require_once CORE_PATH . '/init/TalisHTTP.php';
(new \Talis\Main\HTTP)->gogogo();
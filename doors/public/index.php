<?php
require_once '../environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/init/bootstrap.php';
require_once CORE_PATH . '/init/TalisHTTP.php';
(new init\TalisHTTP)->gogogo();
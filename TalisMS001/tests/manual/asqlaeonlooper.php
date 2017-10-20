<?php
require_once '../../../config/environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
use function \Talis\Logger\dbgr;

class BaseLooper extends \Talis\Services\Sql\aAeonLooper{}

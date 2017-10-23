<?php
require_once '../../../config/environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
use function \Talis\Logger\dbgr;

class BaseLooper extends \Talis\Services\Sql\aAeonLooper{
	protected $db_connection_name = 'samsara';
	protected function query(){
		return "
			SELECT * FROM lms3users.rbac_user
		";
	}
}


//init connection
\Talis\Services\Sql\Factory::getConnectionMySQL('samsara',[
		'host' => '127.0.0.1',
		'database' => 'lms3users',
		'username' => 'root',
		'password' => '123456!!',
		'verbosity' => 2
]);

//initi session which is used for caching the pager
\Talis\Services\Session\Client::start();

//TEST
BaseLooper::autoPagingManipulatedData();

<?php
class Monitor_Process_All{
public function __construct(){
die;
}
}

/**
 * Private bootstrap file for the api subdomain.
 */
set_logger('API_WEB_');
require_once DOMAIN_PATH . '/init/Talis.php';
(new Talis)->gogogo();

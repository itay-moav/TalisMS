<?php
//Figure out the port we listen on
$port = '8000';
$url = "http://localhost:{$port}";
require_once '../../config/bootstrap.php';
(new \Talis\Doors\Rest)->gogogo($url);

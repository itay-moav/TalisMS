<?php

if (preg_match('/\.(?:png|jpg|jpeg|gif|js|html|css)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}
require_once '../../config/bootstrap.php';

//IF you want all the API to be under a sub directory or a few level deep i.e. /api or /api/v0

// localhost:8000/api/talis/discovery
(new \Talis\Doors\Rest)->gogogo('/api');

// localhost:8000/api/v0/talis/discovery
// (new \Talis\Doors\Rest)->gogogo('/api/v0');

// localhost:8000/talis/discovery
// (new \Talis\Doors\Rest)->gogogo('');

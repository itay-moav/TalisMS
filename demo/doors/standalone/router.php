<?php

declare(strict_types=1);

/**
 * Router for `php -S host:port demo/doors/standalone/router.php`, run from
 * the repo root. Routes /api and /http to the matching door; / serves the
 * demo web page; anything else is returned as-is so the built-in server
 * can serve it as a static file (or 404 if it does not exist).
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = (string) parse_url(is_string($requestUri) ? $requestUri : '/', PHP_URL_PATH);
if ($uri === '') {
    $uri = '/';
}

if ($uri === '/') {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/../../web/index.html');
    return true;
}

if ($uri === '/api' || str_starts_with($uri, '/api/')) {
    require __DIR__ . '/../../config/bootstrap.php';
    (new \Talis\Doors\Rest())->gogogo('/api');
    return true;
}

if ($uri === '/http' || str_starts_with($uri, '/http/')) {
    require __DIR__ . '/../../config/bootstrap.php';
    (new \Talis\Doors\HTTP())->gogogo('/http');
    return true;
}

return false;

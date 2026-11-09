<?php

declare(strict_types=1);

/**
 * Rest door entry point for Apache/nginx. Point the vhost's document root
 * here (or rewrite every request to this file) and the door is served
 * from the domain root.
 */

require __DIR__ . '/../../config/bootstrap.php';

(new \Talis\Doors\Rest())->gogogo('');

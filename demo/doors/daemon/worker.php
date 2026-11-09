<?php

declare(strict_types=1);

/**
 * Daemon door entry point: feeds demo/var/spool to the Daemon door.
 *
 * Usage:
 *   php demo/doors/daemon/worker.php          runs until SIGTERM/SIGINT
 *   php demo/doors/daemon/worker.php --once   one pass over incoming/, then exits
 */

require __DIR__ . '/../../config/bootstrap.php';

use Talis\Doors\SpoolWorker;

$worker = new SpoolWorker(DEMO_VAR . '/spool');

$arguments = $_SERVER['argv'] ?? [];
if (!is_array($arguments)) {
    $arguments = [];
}

if (in_array('--once', $arguments, true)) {
    $handled = $worker->runOnce();
    echo "Handled {$handled} file(s)\n";
    exit(0);
}

$worker->run();

<?php

declare(strict_types=1);

/**
 * Demo app settings.
 * DEMO_VAR holds the data file, the logs and the spool folders.
 * Set TALIS_DEMO_VAR to put them somewhere else (the tests use a temp folder).
 */
define('DEMO_ROOT', dirname(__DIR__));
// getenv() gives false when the variable is not set; (string) turns that into ''.
define('DEMO_VAR', (string) getenv('TALIS_DEMO_VAR') ?: DEMO_ROOT . '/var');

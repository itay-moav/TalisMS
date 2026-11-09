<?php

declare(strict_types=1);

/**
 * Every demo door requires this file first.
 * It loads the library and the config, teaches PHP where the app classes
 * live, and sets up TalisMain for the app.
 */

use Demo\DemoLogger;
use Demo\User\UserStore;
use Talis\Message\Request;
use Talis\TalisMain;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/config.php';

// The app loads its own classes: Demo\User\UserStore is in application/model/User/UserStore.php.
// (Api\ classes are loaded by the router.)
spl_autoload_register(static function (string $class): void {
    $prefix = 'Demo\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = DEMO_ROOT . '/application/model/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// The router looks for API classes under <appPath>/api.
TalisMain::$appPath = DEMO_ROOT . '/application';

TalisMain::setLogger(new DemoLogger(DEMO_VAR . '/log/demo.log'));

// Runs once per request, after the route is found. A good place to put
// shared resources (here, the user store) in the Context.
TalisMain::$registeredInitFunc = static function (Request $Request): void {
    TalisMain::$context->set('userStore', new UserStore(DEMO_VAR . '/data/users.json'));
};

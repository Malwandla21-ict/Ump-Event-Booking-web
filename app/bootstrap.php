<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Config;
use App\Core\Env;
use App\Core\ErrorHandler;

define('BASE_PATH', dirname(__DIR__));

// PSR-4 style autoloader for the App\ namespace (no Composer required).
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/helpers.php';

Env::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/config');

date_default_timezone_set(Config::get('app.timezone'));

ErrorHandler::register(
    debug: (bool) Config::get('app.debug'),
    logDir: BASE_PATH . '/storage/logs',
);

return new App(require BASE_PATH . '/routes/web.php');

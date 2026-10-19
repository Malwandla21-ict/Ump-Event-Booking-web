<?php

declare(strict_types=1);

/**
 * Front controller: every request that isn't a real file ends up here.
 */

// Let PHP's built-in dev server serve static assets directly.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($file !== __DIR__ . '/' && is_file($file)) {
        return false;
    }
}

$app = require dirname(__DIR__) . '/app/bootstrap.php';

$app->run();

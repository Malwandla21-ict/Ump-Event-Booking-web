<?php

declare(strict_types=1);

/**
 * Creates the database, tables and (optionally) demo data.
 *
 *   php bin/setup.php            # schema + demo data
 *   php bin/setup.php --no-seed  # schema only
 *
 * WARNING: drops and recreates the app's tables.
 */

use App\Core\Config;
use App\Core\Env;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (str_starts_with($class, 'App\\') && is_file($file)) {
        require $file;
    }
});

Env::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/config');
$db = Config::get('database');
$seed = !in_array('--no-seed', $argv, true);

if (!preg_match('/^\w+$/', $db['database'])) {
    fwrite(STDERR, "DB_DATABASE may only contain letters, numbers and underscores.\n");
    exit(1);
}

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']),
        $db['username'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Could not connect to MySQL at {$db['host']}:{$db['port']} — is it running, and are the credentials in .env correct?\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$files = ['schema.sql', ...($seed ? ['seed.sql'] : [])];

foreach ($files as $file) {
    $sql = file_get_contents(BASE_PATH . '/database/' . $file);
    $sql = str_replace('`ump_event_booking`', '`' . $db['database'] . '`', $sql);
    $sql = preg_replace('/^--.*$/m', '', $sql);

    foreach (preg_split('/;\s*$/m', $sql) as $statement) {
        if (trim($statement) !== '') {
            $pdo->exec($statement);
        }
    }
    echo "  ✓ ran database/$file\n";
}

echo "\nDatabase `{$db['database']}` is ready.\n";
if ($seed) {
    echo "Demo logins (password: Password123):\n  admin@ump.test   (admin)\n  student@ump.test (student)\n";
}

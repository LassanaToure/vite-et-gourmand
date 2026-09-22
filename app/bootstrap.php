<?php
declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

date_default_timezone_set('Europe/Paris');

require BASE_PATH . '/app/helpers.php';
require BASE_PATH . '/app/config/database.php';

if (is_production()) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}

spl_autoload_register(static function (string $class): void {
    foreach (['models', 'services', 'controllers'] as $directory) {
        $file = BASE_PATH . '/app/' . $directory . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

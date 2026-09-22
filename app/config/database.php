<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $required = static function (string $name): string {
        $value = getenv($name);
        if ($value === false || $value === '') {
            throw new RuntimeException('Variable d\'environnement manquante : ' . $name);
        }
        return $value;
    };

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $required('DB_HOST'),
        getenv('DB_PORT') ?: '3306',
        $required('DB_NAME')
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    $ca = getenv('DB_SSL_CA');
    if ($ca !== false && $ca !== '') {
        $path = sys_get_temp_dir() . '/aiven-ca.pem';
        if (!is_file($path)) {
            file_put_contents($path, $ca);
        }
        $options[PDO::MYSQL_ATTR_SSL_CA] = $path;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $pdo = new PDO($dsn, $required('DB_USER'), $required('DB_PASSWORD'), $options);

    return $pdo;
}

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

    $jawsdb = getenv('JAWSDB_URL');
    if ($jawsdb !== false && $jawsdb !== '') {
        $parts = parse_url($jawsdb);
        $host = $parts['host'] ?? '';
        $port = (string) ($parts['port'] ?? 3306);
        $name = ltrim($parts['path'] ?? '', '/');
        $user = rawurldecode($parts['user'] ?? '');
        $password = rawurldecode($parts['pass'] ?? '');
    } else {
        $host = $required('DB_HOST');
        $port = getenv('DB_PORT') ?: '3306';
        $name = $required('DB_NAME');
        $user = $required('DB_USER');
        $password = $required('DB_PASSWORD');
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);

    return $pdo;
}

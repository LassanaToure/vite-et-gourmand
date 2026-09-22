<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    respond(405, ['error' => 'Méthode non autorisée.']);
}

try {
    $menus = MenuPresenter::cards((new MenuModel())->search(MenuFilters::fromQuery($_GET)));
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    respond(500, ['error' => 'Service temporairement indisponible.']);
}

respond(200, ['count' => count($menus), 'menus' => $menus]);

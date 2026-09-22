<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requested = __DIR__ . (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
    if (is_file($requested)) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

Session::start();
send_security_headers();

$routes = require BASE_PATH . '/app/config/routes.php';
$errors = require BASE_PATH . '/app/config/errors.php';
$path = current_path();

$route = null;
$params = [];
foreach ($routes as $candidate) {
    if (preg_match($candidate['pattern'], $path, $matches)) {
        $route = $candidate;
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        break;
    }
}

$result = null;
if ($route !== null) {
    if (is_post() && !Csrf::verify($_POST['_csrf'] ?? null)) {
        $result = ['error' => 'csrf'];
    } else {
        $result = Guard::check($route['access'] ?? null);
    }

    if ($result === null) {
        try {
            $controller = $route['controller'] ?? static fn (): array => [];
            $result = call_user_func($controller, $params, $_GET);
        } catch (Throwable $exception) {
            error_log((string) $exception);
            $result = ['error' => 'server'];
        }
    }
}

if (isset($result['redirect'])) {
    header('Location: ' . url($result['redirect']), true, 303);
    exit;
}

if (isset($result['json'])) {
    http_response_code($result['status'] ?? 200);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($result['json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$vars = [];
if ($route === null || $result === null) {
    http_response_code(404);
    $route = ['view' => 'not-found', 'title' => 'Page introuvable', 'description' => 'Cette page n\'existe pas.'];
} elseif (isset($result['error'])) {
    $error = $errors[$result['error']];
    http_response_code($error['status']);
    $route = ['view' => 'error', 'title' => $error['heading'], 'description' => $error['message']];
    $vars = ['code' => $error['status'], 'heading' => $error['heading'], 'message' => $error['message']];
} else {
    http_response_code($result['status'] ?? 200);
    foreach ($result['headers'] ?? [] as $name => $value) {
        header($name . ': ' . $value);
    }
    $vars = $result['vars'] ?? [];
    $route['title'] = $result['title'] ?? $route['title'];
    $route['description'] = $result['description'] ?? $route['description'];
}

$content = render('pages/' . $route['view'], $params + $vars);

echo render('layouts/' . ($route['layout'] ?? 'main'), [
    'title' => $route['title'],
    'description' => $route['description'],
    'page' => $route['view'],
    'content' => $content,
]);

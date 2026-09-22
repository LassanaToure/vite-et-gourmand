<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $file = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $public = str_replace('\\', '/', BASE_PATH . '/public');
    $relative = str_starts_with($file, $public) ? substr($file, strlen($public)) : '/' . basename($script);

    return rtrim(substr($script, 0, max(0, strlen($script) - strlen($relative))), '/');
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function site(): array
{
    static $site = null;
    return $site ??= require BASE_PATH . '/app/data/site.php';
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_url();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return '/' . trim($path, '/');
}

function is_current(string $path): bool
{
    $current = current_path();
    return $path === '/' ? $current === '/' : str_starts_with($current, $path);
}

function current_user(): ?array
{
    return Auth::user();
}

function csrf_field(): string
{
    return Csrf::field();
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input_string(array $source, string $key, bool $trim = true): string
{
    $value = $source[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    return $trim ? trim($value) : $value;
}

function input_array(array $source, string $key): array
{
    $value = $source[$key] ?? [];

    return is_array($value) ? $value : [];
}

function safe_return_path(mixed $path): string
{
    $valid = is_string($path)
        && $path !== ''
        && $path[0] === '/'
        && !str_starts_with($path, '//')
        && !str_contains($path, '\\')
        && preg_match('/[\x00-\x1f]/', $path) !== 1;

    return $valid ? $path : '/';
}

function app_url(string $path = ''): string
{
    $root = getenv('APP_URL') ?: 'http://localhost:8080';

    return rtrim($root, '/') . base_url() . '/' . ltrim($path, '/');
}

function is_production(): bool
{
    return getenv('APP_ENV') === 'production';
}

function csp_nonce(): string
{
    static $nonce = null;

    return $nonce ??= base64_encode(random_bytes(16));
}

function send_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    if (is_production()) {
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self' 'nonce-" . csp_nonce() . "'; connect-src 'self'");
    }
}

function render(string $template, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require BASE_PATH . '/app/views/' . $template . '.php';
    return (string) ob_get_clean();
}

function partial(string $name, array $vars = []): void
{
    echo render('partials/' . $name, $vars);
}

function icon(string $name, string $class = 'icon'): void
{
    partial('icon', ['name' => $name, 'class' => $class]);
}

function price(float $value): string
{
    $decimals = $value == floor($value) ? 0 : 2;
    return number_format($value, $decimals, ',', "\u{202F}") . "\u{00A0}€";
}

function money(int $cents): string
{
    return number_format($cents / 100, 2, ',', "\u{202F}") . "\u{00A0}€";
}

function format_date(string $date): string
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    return $parsed === false ? $date : $parsed->format('d/m/Y');
}

function format_datetime(string $dateTime): string
{
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTime);

    return $parsed === false ? $dateTime : $parsed->format('d/m/Y') . ' à ' . $parsed->format('H\hi');
}

function order_config(): array
{
    static $config = null;
    return $config ??= require BASE_PATH . '/app/config/order.php';
}

function order_statuses(): array
{
    static $statuses = null;
    return $statuses ??= require BASE_PATH . '/app/config/order_status.php';
}

function normalize_text(string $value): string
{
    $plain = strtr(mb_strtolower(trim($value)), [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ÿ' => 'y',
        'œ' => 'oe', 'æ' => 'ae', '’' => ' ',
    ]);

    return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $plain));
}

function format_hour(?string $time): ?string
{
    if ($time === null) {
        return null;
    }

    [$hour, $minutes] = array_map('intval', explode(':', $time));

    return $minutes === 0 ? $hour . 'h' : $hour . 'h' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT);
}

function opening_hours(): array
{
    static $hours = null;
    if ($hours !== null) {
        return $hours;
    }

    try {
        $rows = (new HoraireModel())->all();
        $hours = array_map(static fn (array $row): array => [
            'day' => $row['jour'],
            'open' => format_hour($row['heure_ouverture']),
            'close' => format_hour($row['heure_fermeture']),
        ], $rows);
    } catch (Throwable) {
        $hours = [];
    }

    return $hours === [] ? ($hours = require BASE_PATH . '/app/data/hours.php') : $hours;
}

function group_hours(array $hours): array
{
    $groups = [];
    foreach ($hours as $row) {
        $label = $row['open'] === null ? 'Fermé' : $row['open'] . ' – ' . $row['close'];
        $last = count($groups) - 1;
        if ($last >= 0 && $groups[$last]['label'] === $label) {
            $groups[$last]['days'][] = $row['day'];
        } else {
            $groups[] = ['days' => [$row['day']], 'label' => $label];
        }
    }
    return array_map(static function (array $group): array {
        $days = $group['days'];
        $group['name'] = count($days) > 1 ? $days[0] . ' – ' . end($days) : $days[0];
        return $group;
    }, $groups);
}

<?php
declare(strict_types=1);

final class Session
{
    private const IDLE_LIMIT = 7200;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('vg_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        $now = time();
        if (isset($_SESSION['_last']) && $now - (int) $_SESSION['_last'] > self::IDLE_LIMIT) {
            self::destroy();
            session_start();
        }
        $_SESSION['_last'] = $now;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?: 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function flash(string $type, string $text): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'text' => $text];
    }

    public static function pullFlash(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return $messages;
    }

    private static function isHttps(): bool
    {
        return ($_SERVER['HTTPS'] ?? '') === 'on'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || is_production();
    }
}

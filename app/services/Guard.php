<?php
declare(strict_types=1);

final class Guard
{
    public static function check(string|array|null $access): ?array
    {
        if ($access === null) {
            return null;
        }

        if ($access === 'guest') {
            return Auth::check() ? ['redirect' => '/'] : null;
        }

        if (!Auth::check()) {
            $query = $_SERVER['QUERY_STRING'] ?? '';
            $target = current_path() . (is_post() || $query === '' ? '' : '?' . $query);

            return ['redirect' => '/connexion?retour=' . rawurlencode($target)];
        }

        if (is_array($access) && !Auth::hasRole(...$access)) {
            return ['error' => 'forbidden'];
        }

        return null;
    }
}

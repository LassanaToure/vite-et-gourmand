<?php
declare(strict_types=1);

final class Csrf
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        return $_SESSION[self::KEY] ??= bin2hex(random_bytes(32));
    }

    public static function rotate(): void
    {
        $_SESSION[self::KEY] = bin2hex(random_bytes(32));
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function verify(mixed $submitted): bool
    {
        $expected = $_SESSION[self::KEY] ?? '';

        return is_string($submitted) && $expected !== '' && hash_equals($expected, $submitted);
    }
}

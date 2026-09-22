<?php
declare(strict_types=1);

final class Auth
{
    private const SESSION_KEY = 'user_id';
    private const DUMMY_HASH = '$2y$12$oKXmXa1RFYZy1.FIQdufn.LKL/TgiufxC1254zChvT/o0nqkbLHEu';

    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;

        $id = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_int($id)) {
            return null;
        }

        $user = (new UserModel())->findById($id);
        if ($user === null || !$user['actif']) {
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }

        return self::$user = $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $role = self::role();

        return $role !== null && in_array($role, $roles, true);
    }

    public static function attempt(string $email, string $password): ?array
    {
        $user = (new UserModel())->findByEmail(mb_strtolower(trim($email)));
        $valid = password_verify($password, $user['password'] ?? self::DUMMY_HASH);

        if ($user === null || !$valid || !$user['actif']) {
            return null;
        }

        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT, ['cost' => 12])) {
            (new UserModel())->updatePassword((int) $user['id'], self::hashPassword($password));
        }
        unset($user['password']);

        return $user;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Csrf::rotate();
        $_SESSION[self::SESSION_KEY] = (int) $user['id'];
        self::$user = $user;
        self::$loaded = true;
    }

    public static function logout(): void
    {
        self::$user = null;
        self::$loaded = false;
        Session::destroy();
        Session::start();
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
}

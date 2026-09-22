<?php
declare(strict_types=1);

final class PasswordPolicy
{
    public const MIN_LENGTH = 10;
    public const MAX_BYTES = 72;

    public static function check(string $password): ?string
    {
        if (strlen($password) > self::MAX_BYTES) {
            return 'Le mot de passe est trop long (' . self::MAX_BYTES . ' caractères maximum).';
        }

        $missing = [];
        if (mb_strlen($password) < self::MIN_LENGTH) {
            $missing[] = self::MIN_LENGTH . ' caractères minimum';
        }
        if (preg_match('/\p{Lu}/u', $password) !== 1) {
            $missing[] = 'une majuscule';
        }
        if (preg_match('/\p{Ll}/u', $password) !== 1) {
            $missing[] = 'une minuscule';
        }
        if (preg_match('/\d/', $password) !== 1) {
            $missing[] = 'un chiffre';
        }
        if (preg_match('/[^\p{L}\p{N}\s]/u', $password) !== 1) {
            $missing[] = 'un caractère spécial';
        }

        return $missing === [] ? null : 'Le mot de passe doit contenir : ' . implode(', ', $missing) . '.';
    }
}

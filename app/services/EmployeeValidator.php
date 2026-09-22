<?php
declare(strict_types=1);

final class EmployeeValidator
{
    public static function forcesRole(array $post): bool
    {
        foreach (array_keys($post) as $key) {
            if (stripos((string) $key, 'role') !== false || stripos((string) $key, 'admin') !== false) {
                return true;
            }
        }

        return false;
    }

    public static function validate(array $input): array
    {
        $errors = [];
        $email = mb_strtolower($input['email']);

        if (strlen($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Saisissez une adresse e-mail valide.';
        }

        $passwordError = PasswordPolicy::check($input['password']);
        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        } elseif ($input['password'] !== $input['password_confirm']) {
            $errors['password_confirm'] = 'Les deux mots de passe ne sont pas identiques.';
        }

        return [$errors, ['email' => $email]];
    }
}

<?php
declare(strict_types=1);

final class AccountController
{
    private const PROFILE_FIELDS = ['nom', 'prenom', 'telephone', 'adresse', 'code_postal', 'ville'];

    public static function show(array $params, array $query): array
    {
        return self::view([], [], self::profileValues(Auth::user()));
    }

    public static function updateProfile(array $params, array $query): array
    {
        if (!is_post()) {
            return ['redirect' => '/mon-compte'];
        }

        $user = Auth::user();
        $input = [];
        foreach (self::PROFILE_FIELDS as $field) {
            $input[$field] = input_string($_POST, $field);
        }

        [$errors, $clean] = RegistrationValidator::validateProfile($input);
        if ($errors === []) {
            (new UserModel())->updateProfile((int) $user['id'], $clean);
            Session::flash('success', 'Vos informations ont été mises à jour.');

            return ['redirect' => '/mon-compte'];
        }

        return self::view($errors, [], $input);
    }

    public static function changePassword(array $params, array $query): array
    {
        if (!is_post()) {
            return ['redirect' => '/mon-compte'];
        }

        $user = Auth::user();
        $current = input_string($_POST, 'current_password', false);
        $new = input_string($_POST, 'password', false);
        $errors = [];

        $hash = (new UserModel())->passwordHash((int) $user['id']);
        if ($hash === null || !password_verify($current, $hash)) {
            $errors['current_password'] = 'Le mot de passe actuel est incorrect.';
        } elseif (($policy = PasswordPolicy::check($new)) !== null) {
            $errors['password'] = $policy;
        } elseif ($new === $current) {
            $errors['password'] = 'Le nouveau mot de passe doit être différent de l\'actuel.';
        } elseif ($new !== input_string($_POST, 'password_confirm', false)) {
            $errors['password_confirm'] = 'Les deux mots de passe ne sont pas identiques.';
        }

        if ($errors !== []) {
            return self::view([], $errors, self::profileValues($user));
        }

        (new UserModel())->updatePassword((int) $user['id'], Auth::hashPassword($new));
        (new PasswordResetModel())->invalidateForUser((int) $user['id']);
        Session::regenerate();
        Csrf::rotate();
        Mailer::send($user['email'], 'Votre mot de passe a été modifié', implode("\n", [
            'Bonjour ' . ($user['prenom'] ?? '') . ',',
            '',
            'Le mot de passe de votre compte Vite & Gourmand vient d\'être modifié.',
            'Si vous n\'êtes pas à l\'origine de ce changement, réinitialisez-le immédiatement : ' . app_url('/mot-de-passe-oublie'),
        ]));
        Session::flash('success', 'Votre mot de passe a été modifié.');

        return ['redirect' => '/mon-compte'];
    }

    private static function view(array $profileErrors, array $passwordErrors, array $values): array
    {
        return [
            'status' => $profileErrors === [] && $passwordErrors === [] ? 200 : 422,
            'vars' => [
                'user' => Auth::user(),
                'profileErrors' => $profileErrors,
                'passwordErrors' => $passwordErrors,
                'values' => $values,
            ],
        ];
    }

    private static function profileValues(array $user): array
    {
        return [
            'nom' => (string) ($user['nom'] ?? ''),
            'prenom' => (string) ($user['prenom'] ?? ''),
            'telephone' => (string) ($user['telephone'] ?? ''),
            'adresse' => (string) ($user['adresse_postale'] ?? ''),
            'code_postal' => (string) ($user['code_postal'] ?? ''),
            'ville' => (string) ($user['ville'] ?? ''),
        ];
    }
}

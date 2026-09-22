<?php
declare(strict_types=1);

final class RegistrationValidator
{
    private const NAME = '/^\p{L}[\p{L}\p{M} \'’\-]{1,49}$/u';
    private const CITY = '/^\p{L}[\p{L}\p{M} \'’\-\.]{1,99}$/u';

    public static function validate(array $input): array
    {
        [$errors, $clean] = self::validateProfile($input);

        $email = mb_strtolower($input['email']);
        if (strlen($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Saisissez une adresse e-mail valide.';
        }
        $clean['email'] = $email;

        $passwordError = PasswordPolicy::check($input['password']);
        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        } elseif ($input['password'] !== $input['password_confirm']) {
            $errors['password_confirm'] = 'Les deux mots de passe ne sont pas identiques.';
        }

        return [$errors, $clean];
    }

    public static function validateProfile(array $input): array
    {
        $errors = [];
        $clean = [];

        foreach (['nom' => 'Le nom', 'prenom' => 'Le prénom'] as $key => $label) {
            if (preg_match(self::NAME, $input[$key]) !== 1) {
                $errors[$key] = $label . ' doit contenir de 2 à 50 lettres (espaces, tirets et apostrophes autorisés).';
            }
            $clean[$key] = $input[$key];
        }

        $phone = self::normalizePhone($input['telephone']);
        if ($phone === null) {
            $errors['telephone'] = 'Saisissez un numéro de GSM valide (ex. : 06 12 34 56 78 ou +32 470 12 34 56).';
        }
        $clean['telephone'] = $phone ?? $input['telephone'];

        if (mb_strlen($input['adresse']) < 5 || mb_strlen($input['adresse']) > 255) {
            $errors['adresse'] = 'Saisissez votre adresse postale (5 à 255 caractères).';
        }
        $clean['adresse'] = $input['adresse'];

        if (preg_match('/^\d{5}$/', $input['code_postal']) !== 1) {
            $errors['code_postal'] = 'Le code postal doit contenir 5 chiffres.';
        }
        $clean['code_postal'] = $input['code_postal'];

        if (!self::isCity($input['ville'])) {
            $errors['ville'] = 'Saisissez le nom de votre ville.';
        }
        $clean['ville'] = $input['ville'];

        return [$errors, $clean];
    }

    public static function isCity(string $city): bool
    {
        return preg_match(self::CITY, $city) === 1;
    }

    public static function normalizePhone(string $raw): ?string
    {
        $phone = preg_replace('/[\s.\-()]/', '', $raw) ?? '';
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        return preg_match('/^(\+[1-9]\d{7,14}|0\d{9})$/', $phone) === 1 ? $phone : null;
    }
}

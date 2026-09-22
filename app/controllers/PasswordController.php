<?php
declare(strict_types=1);

final class PasswordController
{
    public static function forgot(array $params, array $query): array
    {
        $errors = [];
        $old = [];

        if (is_post()) {
            $email = mb_strtolower(input_string($_POST, 'email'));

            if (strlen($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Saisissez une adresse e-mail valide.';
                $old['email'] = $email;
            } else {
                self::sendResetLink($email);
                Session::flash('info', 'Si un compte correspond à cette adresse, un e-mail contenant un lien de réinitialisation vient d\'être envoyé. Le lien est valable une heure.');

                return ['redirect' => '/mot-de-passe-oublie'];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['errors' => $errors, 'old' => $old],
        ];
    }

    public static function reset(array $params, array $query): array
    {
        $token = is_post() ? input_string($_POST, 'token', false) : input_string($query, 'token', false);
        $resets = new PasswordResetModel();
        $record = preg_match('/^[a-f0-9]{64}$/', $token) === 1 ? $resets->findValid(hash('sha256', $token)) : null;
        $headers = ['Referrer-Policy' => 'no-referrer'];

        if ($record === null) {
            return ['status' => 400, 'headers' => $headers, 'vars' => ['invalid' => true, 'token' => '', 'errors' => []]];
        }

        $errors = [];
        if (is_post()) {
            $password = input_string($_POST, 'password', false);
            $passwordError = PasswordPolicy::check($password);

            if ($passwordError !== null) {
                $errors['password'] = $passwordError;
            } elseif ($password !== input_string($_POST, 'password_confirm', false)) {
                $errors['password_confirm'] = 'Les deux mots de passe ne sont pas identiques.';
            }

            if ($errors === []) {
                $userId = (int) $record['utilisateur_id'];
                (new UserModel())->updatePassword($userId, Auth::hashPassword($password));
                $resets->invalidateForUser($userId);
                Session::flash('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');

                return ['redirect' => '/connexion'];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'headers' => $headers,
            'vars' => ['invalid' => false, 'token' => $token, 'errors' => $errors],
        ];
    }

    private static function sendResetLink(string $email): void
    {
        $user = (new UserModel())->findByEmail($email);
        if ($user === null || !$user['actif']) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $resets = new PasswordResetModel();
        $resets->invalidateForUser((int) $user['id']);
        $resets->create((int) $user['id'], hash('sha256', $token));

        $link = app_url('/reinitialiser-mot-de-passe') . '?token=' . $token;
        Mailer::send(
            $email,
            'Réinitialisation de votre mot de passe',
            "Bonjour,\n\nPour choisir un nouveau mot de passe, ouvrez ce lien (valable une heure) :\n{$link}\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, ignorez simplement ce message."
        );
    }
}

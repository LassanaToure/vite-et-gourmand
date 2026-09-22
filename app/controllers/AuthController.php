<?php
declare(strict_types=1);

final class AuthController
{
    private const REGISTER_FIELDS = ['nom', 'prenom', 'email', 'telephone', 'adresse', 'code_postal', 'ville'];

    public static function login(array $params, array $query): array
    {
        $return = safe_return_path($_POST['retour'] ?? $query['retour'] ?? null);
        $errors = [];
        $old = [];

        if (is_post()) {
            $email = input_string($_POST, 'email');
            $user = Auth::attempt($email, input_string($_POST, 'password', false));

            if ($user !== null) {
                Auth::login($user);
                Session::flash('success', 'Bonjour ' . ($user['prenom'] ?? $user['email']) . ', vous êtes connecté.');

                $staff = Auth::hasRole('employe', 'administrateur');

                return ['redirect' => $return === '/' && $staff ? '/admin/commandes' : $return];
            }

            $errors['form'] = 'Identifiants incorrects.';
            $old['email'] = $email;
        }

        return [
            'status' => $errors === [] ? 200 : 401,
            'vars' => ['errors' => $errors, 'old' => $old, 'return' => $return],
        ];
    }

    public static function register(array $params, array $query): array
    {
        $errors = [];
        $old = [];

        if (is_post()) {
            $input = ['password' => input_string($_POST, 'password', false), 'password_confirm' => input_string($_POST, 'password_confirm', false)];
            foreach (self::REGISTER_FIELDS as $field) {
                $input[$field] = input_string($_POST, $field);
            }

            [$errors, $clean] = RegistrationValidator::validate($input);
            $users = new UserModel();

            if (!isset($errors['email']) && $users->emailExists($clean['email'])) {
                $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
            }

            if ($errors === []) {
                try {
                    $id = $users->create($clean + ['password_hash' => Auth::hashPassword($input['password'])]);
                } catch (PDOException $exception) {
                    if (($exception->errorInfo[1] ?? 0) !== 1062) {
                        throw $exception;
                    }
                    $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
                }
            }

            if ($errors === []) {
                Auth::login($users->findById($id));
                Mailer::send($clean['email'], 'Bienvenue chez Vite & Gourmand', self::welcomeMessage($clean['prenom']));
                Session::flash('success', 'Votre compte a été créé. Bienvenue, ' . $clean['prenom'] . ' !');

                return ['redirect' => '/'];
            }

            $old = array_intersect_key($input, array_flip(self::REGISTER_FIELDS));
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['errors' => $errors, 'old' => $old],
        ];
    }

    public static function logout(array $params, array $query): array
    {
        if (is_post()) {
            Auth::logout();
            Session::flash('success', 'Vous avez été déconnecté.');
        }

        return ['redirect' => '/'];
    }

    private static function welcomeMessage(string $firstName): string
    {
        return "Bonjour {$firstName},\n\n"
            . "Votre compte Vite & Gourmand a bien été créé. Vous pouvez dès à présent découvrir nos menus et passer commande : "
            . app_url('/menus') . "\n\n"
            . "À très bientôt,\nJulie et José";
    }
}

<?php
declare(strict_types=1);

final class AdminEmployeeController
{
    public static function index(array $params, array $query): array
    {
        return ['vars' => ['employees' => (new EmployeeModel())->list()]];
    }

    public static function form(array $params, array $query): array
    {
        $errors = [];
        $old = [];

        if (is_post()) {
            $input = [
                'email' => input_string($_POST, 'email'),
                'password' => input_string($_POST, 'password', false),
                'password_confirm' => input_string($_POST, 'password_confirm', false),
            ];
            $old = ['email' => $input['email']];

            if (EmployeeValidator::forcesRole($_POST)) {
                $errors['form'] = 'Création refusée : cette interface crée uniquement des comptes employés, le rôle ne peut pas être choisi.';
            } else {
                [$errors, $clean] = EmployeeValidator::validate($input);
                if (!isset($errors['email']) && (new UserModel())->emailExists($clean['email'])) {
                    $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
                }
            }

            if ($errors === []) {
                try {
                    (new EmployeeModel())->create($clean['email'], Auth::hashPassword($input['password']));
                } catch (PDOException $exception) {
                    if (($exception->errorInfo[1] ?? 0) !== 1062) {
                        throw $exception;
                    }
                    $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
                }
            }

            if ($errors === []) {
                Mailer::send($clean['email'], 'Votre compte employé Vite & Gourmand', self::creationMessage());
                Session::flash('success', 'Le compte employé ' . $clean['email'] . ' a été créé. Communiquez-lui son mot de passe par un autre canal.');

                return ['redirect' => '/admin/employes'];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['errors' => $errors, 'old' => $old],
        ];
    }

    public static function deactivate(array $params, array $query): ?array
    {
        return self::toggle((int) $params['id'], false, 'Le compte est désactivé : la connexion est impossible et les sessions ouvertes sont fermées.');
    }

    public static function reactivate(array $params, array $query): ?array
    {
        return self::toggle((int) $params['id'], true, 'Le compte est réactivé.');
    }

    private static function toggle(int $id, bool $active, string $message): ?array
    {
        if (!is_post()) {
            return ['redirect' => '/admin/employes'];
        }

        $target = (new UserModel())->findById($id);
        if ($target === null) {
            return null;
        }
        if ($target['role'] !== 'employe' || !(new EmployeeModel())->setActive($id, $active)) {
            return ['error' => 'forbidden'];
        }

        Session::flash('success', $message);

        return ['redirect' => '/admin/employes'];
    }

    private static function creationMessage(): string
    {
        return "Bonjour,\n\n"
            . "Un compte employé Vite & Gourmand vient d'être créé pour cette adresse e-mail, qui sert d'identifiant de connexion. "
            . "Votre mot de passe vous sera communiqué séparément par l'administrateur.\n\n"
            . 'Connexion : ' . app_url('/connexion') . "\n\n"
            . 'Julie et José';
    }
}

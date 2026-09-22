<?php
declare(strict_types=1);

final class ContactController
{
    public static function index(array $params, array $query): array
    {
        $errors = [];
        $old = [];

        if (is_post()) {
            $objet = input_string($_POST, 'objet');
            $message = input_string($_POST, 'message', false);
            $email = mb_strtolower(input_string($_POST, 'email'));
            $old = ['objet' => $objet, 'message' => $message, 'email' => $email];

            if (mb_strlen($objet) < 3 || mb_strlen($objet) > 150) {
                $errors['objet'] = 'Indiquez un objet (3 à 150 caractères).';
            }
            if (mb_strlen(trim($message)) < 10 || mb_strlen($message) > 2000) {
                $errors['message'] = 'Décrivez votre demande (10 à 2000 caractères).';
            }
            if (strlen($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Saisissez une adresse e-mail valide.';
            }

            if ($errors === []) {
                Mailer::send(site()['email'], 'Contact site : ' . $objet, self::body($objet, $message, $email));
                Session::flash('success', 'Votre message a été envoyé. Nous vous répondrons dans les 24 heures.');

                return ['redirect' => '/contact'];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['errors' => $errors, 'old' => $old],
        ];
    }

    private static function body(string $objet, string $message, string $email): string
    {
        return "Nouveau message depuis le formulaire de contact du site.\n\n"
            . "Objet : {$objet}\n"
            . "De : {$email}\n\n"
            . $message;
    }
}

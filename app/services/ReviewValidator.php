<?php
declare(strict_types=1);

final class ReviewValidator
{
    public static function validate(array $input): array
    {
        $errors = [];

        $rating = preg_match('/^[1-5]$/', $input['note']) === 1 ? (int) $input['note'] : 0;
        if ($rating === 0) {
            $errors['note'] = 'Choisissez une note de 1 à 5.';
        }

        $comment = $input['commentaire'];
        if (mb_strlen($comment) < 10 || mb_strlen($comment) > 1000) {
            $errors['commentaire'] = 'Votre commentaire doit contenir entre 10 et 1000 caractères.';
        }

        return [$errors, ['note' => $rating, 'commentaire' => $comment]];
    }
}

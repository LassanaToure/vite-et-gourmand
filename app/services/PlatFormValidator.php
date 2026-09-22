<?php
declare(strict_types=1);

final class PlatFormValidator
{
    public static function validate(array $input, array $references): array
    {
        $errors = [];

        $title = $input['titre_plat'];
        if (mb_strlen($title) < 2 || mb_strlen($title) > 100) {
            $errors['titre_plat'] = 'Le libellé doit contenir de 2 à 100 caractères.';
        }

        if (!in_array($input['categorie'], ['entree', 'plat', 'dessert'], true)) {
            $errors['categorie'] = 'Choisissez le type de plat (entrée, plat ou dessert).';
        }

        return [$errors, [
            'titre_plat' => $title,
            'categorie' => $input['categorie'],
            'allergenes' => self::subset($input['allergenes'], $references['allergenes']),
            'menus' => self::subset($input['menus'], $references['menus']),
        ]];
    }

    private static function subset(array $posted, array $allowed): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', array_filter($posted, 'is_string')),
            static fn (int $id): bool => in_array($id, $allowed, true)
        )));
    }
}

<?php
declare(strict_types=1);

final class MenuFormValidator
{
    public static function validate(array $input, array $references): array
    {
        $errors = [];
        $clean = [];

        $title = $input['titre'];
        if (mb_strlen($title) < 3 || mb_strlen($title) > 100) {
            $errors['titre'] = 'Le titre doit contenir de 3 à 100 caractères.';
        }
        $clean['titre'] = $title;

        $texts = [
            'description' => [20, 'La description doit contenir de 20 à 2000 caractères.'],
            'conditions' => [5, 'Les conditions doivent contenir de 5 à 2000 caractères.'],
        ];
        foreach ($texts as $key => [$min, $message]) {
            if (mb_strlen($input[$key]) < $min || mb_strlen($input[$key]) > 2000) {
                $errors[$key] = $message;
            }
            $clean[$key] = $input[$key];
        }

        foreach (['theme_id' => 'thème', 'regime_id' => 'régime'] as $key => $label) {
            $id = self::integer($input[$key], 1, 999999);
            if ($id === null || !in_array($id, $references[$key], true)) {
                $errors[$key] = 'Choisissez un ' . $label . ' dans la liste.';
            }
            $clean[$key] = $id ?? 0;
        }

        $minimum = self::integer($input['nombre_personne_minimum'], 1, 1000);
        if ($minimum === null) {
            $errors['nombre_personne_minimum'] = 'Le nombre minimum de personnes doit être compris entre 1 et 1000.';
        }
        $clean['nombre_personne_minimum'] = $minimum ?? 0;

        $price = str_replace(',', '.', $input['prix_par_personne']);
        if (preg_match('/^\d{1,4}(\.\d{1,2})?$/', $price) !== 1 || (float) $price <= 0) {
            $errors['prix_par_personne'] = 'Saisissez un prix par personne valide (ex. : 54 ou 54,50).';
        }
        $clean['prix_par_personne'] = number_format((float) $price, 2, '.', '');

        $stock = self::integer($input['quantite_restante'], 0, 9999);
        if ($stock === null) {
            $errors['quantite_restante'] = 'Le stock doit être un nombre entre 0 et 9999.';
        }
        $clean['quantite_restante'] = $stock ?? 0;

        $delay = self::integer($input['delai_minimum_jours'], 0, 365);
        if ($delay === null) {
            $errors['delai_minimum_jours'] = 'Le délai de commande doit être un nombre de jours entre 0 et 365.';
        }
        $clean['delai_minimum_jours'] = $delay ?? 0;

        $clean['plats'] = array_values(array_unique(array_filter(
            array_map('intval', array_filter($input['plats'], 'is_string')),
            static fn (int $id): bool => in_array($id, $references['plats'], true)
        )));

        return [$errors, $clean];
    }

    private static function integer(string $raw, int $min, int $max): ?int
    {
        if (preg_match('/^\d{1,9}$/', $raw) !== 1) {
            return null;
        }
        $value = (int) $raw;

        return $value >= $min && $value <= $max ? $value : null;
    }
}

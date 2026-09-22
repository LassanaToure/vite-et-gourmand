<?php
declare(strict_types=1);

final class OrderValidator
{
    public static function earliestDate(int $delayDays): string
    {
        return (new DateTimeImmutable('today'))->modify('+' . $delayDays . ' days')->format('Y-m-d');
    }

    public static function validate(array $input, ?array $menu, array $options = []): array
    {
        $config = order_config();
        $checkStock = $options['check_stock'] ?? true;
        $keepDate = $options['keep_date'] ?? null;
        $errors = [];
        $clean = [];

        if ($menu === null) {
            $errors['menu_id'] = 'Choisissez un menu.';
        } elseif ($checkStock && (int) $menu['quantite_restante'] < 1) {
            $errors['menu_id'] = 'Ce menu n\'est plus disponible pour le moment.';
        }
        $clean['menu_id'] = $menu === null ? 0 : (int) $menu['menu_id'];

        $phone = RegistrationValidator::normalizePhone($input['telephone']);
        if ($phone === null) {
            $errors['telephone'] = 'Saisissez un numéro de GSM valide (ex. : 06 12 34 56 78 ou +32 470 12 34 56).';
        }
        $clean['telephone'] = $phone ?? $input['telephone'];

        $address = $input['adresse'];
        if (mb_strlen($address) < 5 || mb_strlen($address) > 255) {
            $errors['adresse'] = 'Saisissez l\'adresse de livraison (5 à 255 caractères).';
        }
        $clean['adresse'] = $address;

        $postcode = $input['code_postal'];
        if (preg_match('/^\d{5}$/', $postcode) !== 1) {
            $errors['code_postal'] = 'Le code postal doit contenir 5 chiffres.';
        }
        $clean['code_postal'] = $postcode;

        $city = $input['ville'];
        if (!RegistrationValidator::isCity($city)) {
            $errors['ville'] = 'Saisissez la ville de livraison.';
        }
        $clean['ville'] = $city;

        $date = self::parseDate($input['date_prestation']);
        if ($date === null) {
            $errors['date_prestation'] = 'Saisissez une date de prestation valide.';
        } elseif ($menu !== null && $date < self::earliestDate((int) $menu['delai_minimum_jours']) && !($date === $keepDate && $date >= date('Y-m-d'))) {
            $errors['date_prestation'] = 'Ce menu doit être commandé au moins ' . (int) $menu['delai_minimum_jours'] . ' jours avant la prestation (à partir du '
                . (new DateTimeImmutable(self::earliestDate((int) $menu['delai_minimum_jours'])))->format('d/m/Y') . ').';
        } elseif ($date > self::earliestDate($config['max_days_ahead'])) {
            $errors['date_prestation'] = 'La date de prestation ne peut pas dépasser un an.';
        }
        $clean['date_prestation'] = $date ?? $input['date_prestation'];

        [$from, $to] = $config['delivery_hours'];
        $time = $input['heure_livraison'];
        if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) !== 1 || $time < $from || $time > $to) {
            $errors['heure_livraison'] = 'Choisissez une heure de livraison entre ' . $from . ' et ' . $to . '.';
        }
        $clean['heure_livraison'] = $time;

        $people = preg_match('/^\d{1,4}$/', $input['nombre_personne']) === 1 ? (int) $input['nombre_personne'] : 0;
        if ($people < 1) {
            $errors['nombre_personne'] = 'Indiquez un nombre de personnes valide.';
        } elseif ($menu !== null && $people < (int) $menu['nombre_personne_minimum']) {
            $errors['nombre_personne'] = 'Ce menu se commande pour ' . (int) $menu['nombre_personne_minimum'] . ' personnes minimum.';
        }
        $clean['nombre_personne'] = $people;

        if ($input['accept'] !== '1') {
            $errors['accept'] = 'Vous devez confirmer avoir pris connaissance des conditions du menu et des CGV.';
        }

        return [$errors, $clean];
    }

    private static function parseDate(string $value): ?string
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $problems = DateTimeImmutable::getLastErrors();
        $valid = $date !== false && ($problems === false || ($problems['warning_count'] === 0 && $problems['error_count'] === 0));

        return $valid && $date->format('Y-m-d') === $value ? $value : null;
    }
}

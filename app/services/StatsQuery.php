<?php
declare(strict_types=1);

final class StatsQuery
{
    public static function filters(array $query): array
    {
        $errors = [];
        $clean = ['menu' => null, 'du' => null, 'au' => null];

        $menu = $query['menu'] ?? '';
        if ($menu !== '') {
            if (is_string($menu) && preg_match('/^[1-9]\d{0,9}$/', $menu) === 1) {
                $clean['menu'] = (int) $menu;
            } else {
                $errors['menu'] = 'Le menu choisi n\'est pas valide : le filtre a été ignoré.';
            }
        }

        foreach (['du' => 'de début', 'au' => 'de fin'] as $key => $label) {
            $value = $query[$key] ?? '';
            if ($value === '') {
                continue;
            }
            if (is_string($value) && self::isDate($value)) {
                $clean[$key] = $value;
            } else {
                $errors[$key] = 'La date ' . $label . ' n\'est pas valide : le filtre a été ignoré.';
            }
        }

        if ($clean['du'] !== null && $clean['au'] !== null && $clean['du'] > $clean['au']) {
            $errors['au'] = 'La date de fin précède la date de début : la période a été ignorée.';
            $clean['du'] = $clean['au'] = null;
        }

        return [$errors, $clean];
    }

    public static function report(array $filters): array
    {
        $rows = array_map(static fn (array $row): array => [
            'menu_id' => (int) $row['_id'],
            'titre' => (string) $row['titre'],
            'commandes' => (int) $row['commandes'],
            'ca_cents' => (int) $row['ca_cents'],
        ], MongoStore::aggregate([
            ['$match' => self::criteria($filters, false)],
            ['$group' => [
                '_id' => '$menu_id',
                'titre' => ['$last' => '$menu_titre'],
                'commandes' => ['$sum' => 1],
                'ca_cents' => ['$sum' => '$montant_cents'],
            ]],
            ['$sort' => ['commandes' => -1, '_id' => 1]],
        ]));

        $cancelledRows = MongoStore::aggregate([
            ['$match' => self::criteria($filters, true)],
            ['$group' => ['_id' => null, 'total' => ['$sum' => 1]]],
        ]);

        return [
            'rows' => $rows,
            'menus' => self::menus(),
            'kpi' => [
                'commandes' => array_sum(array_column($rows, 'commandes')),
                'ca_cents' => array_sum(array_column($rows, 'ca_cents')),
                'top' => $rows[0] ?? null,
                'annulees' => (int) ($cancelledRows[0]['total'] ?? 0),
            ],
        ];
    }

    private static function criteria(array $filters, bool $cancelled): array
    {
        $criteria = ['annulee' => $cancelled];
        if ($filters['menu'] !== null) {
            $criteria['menu_id'] = $filters['menu'];
        }
        $period = self::period($filters);
        if ($period !== []) {
            $criteria['date_commande'] = $period;
        }

        return $criteria;
    }

    private static function menus(): array
    {
        return array_map(static fn (array $row): array => [
            'menu_id' => (int) $row['_id'],
            'titre' => (string) $row['titre'],
        ], MongoStore::aggregate([
            ['$group' => ['_id' => '$menu_id', 'titre' => ['$last' => '$menu_titre']]],
            ['$sort' => ['titre' => 1]],
        ]));
    }

    private static function period(array $filters): array
    {
        $period = [];
        if ($filters['du'] !== null) {
            $period['$gte'] = OrderStatsSync::day($filters['du']);
        }
        if ($filters['au'] !== null) {
            $period['$lt'] = OrderStatsSync::day(date('Y-m-d', (int) strtotime($filters['au'] . ' +1 day')));
        }

        return $period;
    }

    private static function isDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value && $value >= '2000-01-01' && $value <= '2100-12-31';
    }
}

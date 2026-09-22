<?php
declare(strict_types=1);

final class OrderTimeline
{
    private const SEQUENCE = ['en_attente', 'accepte', 'en_preparation', 'en_cours_livraison', 'livre', 'attente_retour_materiel', 'terminee'];

    public static function build(array $order, array $rows): array
    {
        $labels = order_statuses();
        $lastIndex = count($rows) - 1;
        $events = [];

        foreach ($rows as $index => $row) {
            $events[] = [
                'status' => $row['statut'],
                'title' => $row['commentaire'] ?? $labels[$row['statut']]['event'],
                'at' => $row['date_modification'],
                'current' => $index === $lastIndex,
            ];
        }

        return ['events' => $events, 'upcoming' => self::upcoming($order)];
    }

    public static function returnDeadline(array $rows): ?string
    {
        $entry = null;
        foreach ($rows as $row) {
            if ($row['statut'] === 'attente_retour_materiel') {
                $entry = $row['date_modification'];
            }
        }
        if ($entry === null) {
            return null;
        }

        return self::addBusinessDays(substr($entry, 0, 10), 10);
    }

    public static function addBusinessDays(string $date, int $days): string
    {
        $current = new DateTimeImmutable($date);
        for ($added = 0; $added < $days;) {
            $current = $current->modify('+1 day');
            if ((int) $current->format('N') < 6) {
                $added++;
            }
        }

        return $current->format('Y-m-d');
    }

    private static function upcoming(array $order): array
    {
        $current = $order['statut'];
        if ($current === 'annulee') {
            return [];
        }

        $sequence = array_values(array_filter(
            self::SEQUENCE,
            static fn (string $status): bool => $status !== 'attente_retour_materiel' || (bool) $order['pret_materiel']
        ));
        $position = array_search($current, $sequence, true);

        return $position === false ? [] : array_slice($sequence, $position + 1);
    }
}

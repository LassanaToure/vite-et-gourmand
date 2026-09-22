<?php
declare(strict_types=1);

use MongoDB\BSON\UTCDateTime;

final class OrderStatsSync extends Model
{
    private const SELECT = 'SELECT c.commande_id, c.numero_commande, c.menu_id, m.titre AS menu_titre,
            c.nombre_personne, c.prix_menu, c.prix_livraison, c.date_commande, c.date_prestation, c.statut
        FROM commande c
        JOIN menu m ON m.menu_id = c.menu_id';

    public function sync(int $orderId): bool
    {
        try {
            $statement = $this->pdo()->prepare(self::SELECT . ' WHERE c.commande_id = :id');
            $statement->execute(['id' => $orderId]);
            $row = $statement->fetch();
            if ($row === false) {
                return false;
            }
            MongoStore::upsert(self::document($row));

            return true;
        } catch (Throwable $exception) {
            error_log('[stats] synchronisation MongoDB impossible pour la commande ' . $orderId . ' : ' . $exception->getMessage());

            return false;
        }
    }

    public function syncAll(): int
    {
        $rows = $this->pdo()->query(self::SELECT . ' ORDER BY c.commande_id')->fetchAll();

        return MongoStore::replaceAll(array_map([self::class, 'document'], $rows));
    }

    public static function day(string $date): UTCDateTime
    {
        return new UTCDateTime(((int) strtotime($date . ' 00:00:00 UTC')) * 1000);
    }

    private static function document(array $row): array
    {
        return [
            '_id' => (int) $row['commande_id'],
            'numero' => (string) $row['numero_commande'],
            'menu_id' => (int) $row['menu_id'],
            'menu_titre' => (string) $row['menu_titre'],
            'personnes' => (int) $row['nombre_personne'],
            'montant_cents' => OrderPricing::cents($row['prix_menu']) + OrderPricing::cents($row['prix_livraison']),
            'date_commande' => self::day((string) $row['date_commande']),
            'date_prestation' => self::day((string) $row['date_prestation']),
            'statut' => (string) $row['statut'],
            'annulee' => $row['statut'] === 'annulee',
            'mis_a_jour' => new UTCDateTime(),
        ];
    }
}

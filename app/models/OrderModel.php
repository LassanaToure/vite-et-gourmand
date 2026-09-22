<?php
declare(strict_types=1);

final class OrderModel extends Model
{
    public function place(int $userId, array $data, array $quote): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $menu = (new MenuModel($pdo))->find($data['menu_id'], true);
            $rejection = $this->rejection($menu, $data);
            if ($rejection !== null) {
                $pdo->rollBack();
                return ['error' => $rejection];
            }

            $pricing = OrderPricing::compute($menu, $data['nombre_personne'], $quote);

            $stock = $pdo->prepare(
                'UPDATE menu SET quantite_restante = quantite_restante - 1
                WHERE menu_id = :id AND quantite_restante > 0'
            );
            $stock->execute(['id' => $menu['menu_id']]);
            if ($stock->rowCount() !== 1) {
                $pdo->rollBack();
                return ['error' => 'stock'];
            }

            $id = $this->insert($userId, $data, $pricing);
            $pdo->commit();
            (new OrderStatsSync($pdo))->sync($id);

            return ['id' => $id, 'numero' => self::number($id), 'pricing' => $pricing, 'menu' => $menu];
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function findForUser(int $orderId, int $userId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT c.*, m.titre AS menu_titre, m.conditions AS menu_conditions, m.delai_minimum_jours
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE c.commande_id = :id AND c.utilisateur_id = :user'
        );
        $statement->execute(['id' => $orderId, 'user' => $userId]);
        $order = $statement->fetch();

        return $order === false ? null : $order;
    }

    public function listForUser(int $userId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT c.commande_id, c.numero_commande, c.date_prestation, c.nombre_personne, c.prix_menu,
                c.prix_livraison, c.statut, m.titre AS menu_titre,
                (SELECT COUNT(*) FROM avis a WHERE a.commande_id = c.commande_id) AS avis_donne
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE c.utilisateur_id = :user
            ORDER BY c.commande_id DESC'
        );
        $statement->execute(['user' => $userId]);

        return $statement->fetchAll();
    }

    public function timeline(int $orderId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT statut, commentaire, date_modification
            FROM commande_suivi
            WHERE commande_id = :id
            ORDER BY date_modification, suivi_id'
        );
        $statement->execute(['id' => $orderId]);

        return $statement->fetchAll();
    }

    public function update(int $orderId, int $userId, array $data, array $quote): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $order = $this->lockOwned($orderId, $userId);
            if ($order === null || $order['statut'] !== 'en_attente') {
                return $this->abort($pdo, $order === null ? 'notfound' : 'status');
            }

            $menu = (new MenuModel($pdo))->find((int) $order['menu_id']);
            if ($menu === null || $data['nombre_personne'] < (int) $menu['nombre_personne_minimum']) {
                return $this->abort($pdo, 'minimum');
            }

            $unit = intdiv(
                OrderPricing::cents($order['prix_menu']) + OrderPricing::cents($order['remise']),
                max(1, (int) $order['nombre_personne'])
            );
            $pricing = OrderPricing::forUnit($unit, (int) $menu['nombre_personne_minimum'], $data['nombre_personne'], $quote);

            $pdo->prepare(
                'UPDATE commande SET date_prestation = :date_prestation, heure_livraison = :heure,
                    adresse_prestation = :adresse, code_postal_prestation = :code_postal, ville_prestation = :ville,
                    telephone_contact = :telephone, prix_menu = :prix_menu, remise = :remise,
                    nombre_personne = :personnes, distance_km = :distance, distance_source = :source,
                    prix_livraison = :livraison
                WHERE commande_id = :id'
            )->execute([
                'date_prestation' => $data['date_prestation'],
                'heure' => $data['heure_livraison'] . ':00',
                'adresse' => $data['adresse'],
                'code_postal' => $data['code_postal'],
                'ville' => $data['ville'],
                'telephone' => $data['telephone'],
                'prix_menu' => self::decimal($pricing['menu']),
                'remise' => self::decimal($pricing['discount']),
                'personnes' => $data['nombre_personne'],
                'distance' => number_format($pricing['km'], 1, '.', ''),
                'source' => $pricing['source'],
                'livraison' => self::decimal($pricing['delivery']),
                'id' => $orderId,
            ]);
            $this->trace($orderId, 'en_attente', 'Commande modifiée par le client');
            $pdo->commit();
            (new OrderStatsSync($pdo))->sync($orderId);

            return ['order' => $order, 'menu' => $menu, 'pricing' => $pricing];
        } catch (Throwable $exception) {
            $this->rollBackOnFailure($pdo);
            throw $exception;
        }
    }

    public function cancel(int $orderId, int $userId): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $order = $this->lockOwned($orderId, $userId);
            if ($order === null || $order['statut'] !== 'en_attente') {
                return $this->abort($pdo, $order === null ? 'notfound' : 'status');
            }

            $pdo->prepare(
                "UPDATE commande SET statut = 'annulee', motif_annulation = 'Annulation à la demande du client'
                WHERE commande_id = :id"
            )->execute(['id' => $orderId]);
            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = :menu')
                ->execute(['menu' => $order['menu_id']]);
            $this->trace($orderId, 'annulee', 'Annulée à la demande du client');
            $pdo->commit();
            (new OrderStatsSync($pdo))->sync($orderId);

            return ['order' => $order];
        } catch (Throwable $exception) {
            $this->rollBackOnFailure($pdo);
            throw $exception;
        }
    }

    private function lockOwned(int $orderId, int $userId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT * FROM commande WHERE commande_id = :id AND utilisateur_id = :user FOR UPDATE'
        );
        $statement->execute(['id' => $orderId, 'user' => $userId]);
        $order = $statement->fetch();

        return $order === false ? null : $order;
    }

    private function trace(int $orderId, string $status, string $comment): void
    {
        $this->pdo()->prepare('INSERT INTO commande_suivi (commande_id, statut, commentaire) VALUES (:id, :statut, :commentaire)')
            ->execute(['id' => $orderId, 'statut' => $status, 'commentaire' => $comment]);
    }

    private function abort(PDO $pdo, string $reason): array
    {
        $pdo->rollBack();

        return ['error' => $reason];
    }

    private function rollBackOnFailure(PDO $pdo): void
    {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    private function rejection(?array $menu, array $data): ?string
    {
        if ($menu === null || !(bool) $menu['actif']) {
            return 'menu';
        }
        if ((int) $menu['quantite_restante'] < 1) {
            return 'stock';
        }
        if ($data['nombre_personne'] < (int) $menu['nombre_personne_minimum']) {
            return 'minimum';
        }
        if ($data['date_prestation'] < OrderValidator::earliestDate((int) $menu['delai_minimum_jours'])) {
            return 'delai';
        }

        return null;
    }

    private function insert(int $userId, array $data, array $pricing): int
    {
        $pdo = $this->pdo();
        $statement = $pdo->prepare(
            "INSERT INTO commande (numero_commande, date_commande, date_prestation, heure_livraison,
                adresse_prestation, code_postal_prestation, ville_prestation, telephone_contact,
                prix_menu, remise, nombre_personne, distance_km, distance_source, prix_livraison,
                statut, utilisateur_id, menu_id)
            VALUES (:numero, :date_commande, :date_prestation, :heure, :adresse, :code_postal, :ville, :telephone,
                :prix_menu, :remise, :personnes, :distance, :source, :livraison, 'en_attente', :user, :menu)"
        );
        $statement->execute([
            'numero' => 'TMP-' . bin2hex(random_bytes(8)),
            'date_commande' => date('Y-m-d'),
            'date_prestation' => $data['date_prestation'],
            'heure' => $data['heure_livraison'] . ':00',
            'adresse' => $data['adresse'],
            'code_postal' => $data['code_postal'],
            'ville' => $data['ville'],
            'telephone' => $data['telephone'],
            'prix_menu' => self::decimal($pricing['menu']),
            'remise' => self::decimal($pricing['discount']),
            'personnes' => $data['nombre_personne'],
            'distance' => number_format($pricing['km'], 1, '.', ''),
            'source' => $pricing['source'],
            'livraison' => self::decimal($pricing['delivery']),
            'user' => $userId,
            'menu' => $data['menu_id'],
        ]);
        $id = (int) $pdo->lastInsertId();

        $pdo->prepare('UPDATE commande SET numero_commande = :numero WHERE commande_id = :id')
            ->execute(['numero' => self::number($id), 'id' => $id]);
        $pdo->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (:id, 'en_attente')")
            ->execute(['id' => $id]);

        return $id;
    }

    private static function number(int $id): string
    {
        return sprintf('VG-%s-%05d', date('Y'), $id);
    }

    private static function decimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}

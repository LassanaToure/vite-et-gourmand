<?php
declare(strict_types=1);

final class OrderAdminModel extends Model
{
    public function listAll(): array
    {
        return $this->pdo()->query(
            'SELECT c.commande_id, c.numero_commande, c.date_prestation, c.nombre_personne, c.prix_menu,
                c.prix_livraison, c.statut, c.pret_materiel, m.titre AS menu_titre,
                u.nom, u.prenom, u.email
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            ORDER BY c.commande_id DESC'
        )->fetchAll();
    }

    public function findAny(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT c.*, m.titre AS menu_titre, m.conditions AS menu_conditions,
                u.nom, u.prenom, u.email, u.telephone AS compte_telephone
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            WHERE c.commande_id = :id'
        );
        $statement->execute(['id' => $id]);
        $order = $statement->fetch();

        return $order === false ? null : $order;
    }

    public function history(int $orderId): array
    {
        $statement = $this->pdo()->prepare(
            "SELECT s.statut, s.commentaire, s.date_modification,
                NULLIF(TRIM(CONCAT_WS(' ', a.prenom, a.nom)), '') AS auteur
            FROM commande_suivi s
            LEFT JOIN utilisateur a ON a.utilisateur_id = s.auteur_id
            WHERE s.commande_id = :id
            ORDER BY s.date_modification, s.suivi_id"
        );
        $statement->execute(['id' => $orderId]);

        return $statement->fetchAll();
    }

    public function advance(int $orderId, string $from, array $options, int $staffId): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $order = $this->lock($orderId);
            if ($order === null) {
                return $this->abort($pdo, 'notfound');
            }
            if ($order['statut'] !== $from) {
                return $this->abort($pdo, 'stale');
            }

            $lend = $from === 'en_cours_livraison' && !empty($options['lend']);
            $order['pret_materiel'] = $from === 'en_cours_livraison' ? $lend : (bool) $order['pret_materiel'];
            $next = OrderFlow::next($order);
            if ($next === null) {
                return $this->abort($pdo, 'final');
            }
            if ($from === 'attente_retour_materiel' && empty($options['returned'])) {
                return $this->abort($pdo, 'confirm');
            }

            $pdo->prepare(
                'UPDATE commande SET statut = :statut, pret_materiel = :lend,
                    restitution_materiel = restitution_materiel OR :returned
                WHERE commande_id = :id'
            )->execute([
                'statut' => $next,
                'lend' => (int) (bool) $order['pret_materiel'],
                'returned' => (int) ($from === 'attente_retour_materiel'),
                'id' => $orderId,
            ]);
            $this->trace($orderId, $next, null, $staffId);
            $pdo->commit();
            (new OrderStatsSync($pdo))->sync($orderId);

            return ['next' => $next, 'lend' => (bool) $order['pret_materiel']];
        } catch (Throwable $exception) {
            $this->rollBackOnFailure($pdo);
            throw $exception;
        }
    }

    public function cancel(int $orderId, string $mode, string $reason, int $staffId): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $order = $this->lock($orderId);
            if ($order === null) {
                return $this->abort($pdo, 'notfound');
            }
            if (!OrderFlow::canCancel($order)) {
                return $this->abort($pdo, 'status');
            }

            $pdo->prepare(
                "UPDATE commande SET statut = 'annulee', motif_annulation = :motif, mode_contact_annulation = :mode
                WHERE commande_id = :id"
            )->execute(['motif' => $reason, 'mode' => $mode, 'id' => $orderId]);
            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = :menu')
                ->execute(['menu' => $order['menu_id']]);
            $this->trace($orderId, 'annulee', null, $staffId);
            $pdo->commit();
            (new OrderStatsSync($pdo))->sync($orderId);

            return ['ok' => true];
        } catch (Throwable $exception) {
            $this->rollBackOnFailure($pdo);
            throw $exception;
        }
    }

    private function lock(int $orderId): ?array
    {
        $statement = $this->pdo()->prepare('SELECT * FROM commande WHERE commande_id = :id FOR UPDATE');
        $statement->execute(['id' => $orderId]);
        $order = $statement->fetch();

        return $order === false ? null : $order;
    }

    private function trace(int $orderId, string $status, ?string $comment, int $staffId): void
    {
        $this->pdo()->prepare(
            'INSERT INTO commande_suivi (commande_id, statut, commentaire, auteur_id)
            VALUES (:id, :statut, :commentaire, :auteur)'
        )->execute(['id' => $orderId, 'statut' => $status, 'commentaire' => $comment, 'auteur' => $staffId]);
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
}

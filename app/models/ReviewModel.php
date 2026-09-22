<?php
declare(strict_types=1);

final class ReviewModel extends Model
{
    public function findForOrder(int $orderId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT avis_id, note, description, statut, date_creation FROM avis WHERE commande_id = :order'
        );
        $statement->execute(['order' => $orderId]);
        $review = $statement->fetch();

        return $review === false ? null : $review;
    }

    public function listAll(): array
    {
        return $this->pdo()->query(
            'SELECT a.avis_id, a.note, a.description, a.statut, a.date_creation,
                u.prenom, u.nom, u.email, c.numero_commande, m.titre AS menu_titre
            FROM avis a
            JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
            JOIN commande c ON c.commande_id = a.commande_id
            JOIN menu m ON m.menu_id = c.menu_id
            ORDER BY a.date_creation DESC, a.avis_id DESC'
        )->fetchAll();
    }

    public function moderate(int $id, string $status): bool
    {
        $exists = $this->pdo()->prepare('SELECT COUNT(*) FROM avis WHERE avis_id = :id');
        $exists->execute(['id' => $id]);
        if ((int) $exists->fetchColumn() === 0) {
            return false;
        }

        $this->pdo()->prepare('UPDATE avis SET statut = :statut WHERE avis_id = :id')
            ->execute(['statut' => $status, 'id' => $id]);

        return true;
    }

    public function published(int $limit): array
    {
        $statement = $this->pdo()->prepare(
            "SELECT a.note, a.description, u.prenom, u.nom, YEAR(u.date_creation) AS depuis
            FROM avis a
            JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
            WHERE a.statut = 'valide'
            ORDER BY a.date_creation DESC, a.avis_id DESC
            LIMIT :limit"
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function create(int $userId, int $orderId, int $rating, string $comment): int
    {
        $statement = $this->pdo()->prepare(
            "INSERT INTO avis (note, description, statut, utilisateur_id, commande_id)
            VALUES (:note, :description, 'en_attente', :user, :order)"
        );
        $statement->execute(['note' => $rating, 'description' => $comment, 'user' => $userId, 'order' => $orderId]);

        return (int) $this->pdo()->lastInsertId();
    }
}

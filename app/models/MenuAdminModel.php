<?php
declare(strict_types=1);

final class MenuAdminModel extends Model
{
    public function listAll(): array
    {
        return $this->pdo()->query(
            'SELECT m.menu_id, m.titre, m.prix_par_personne, m.nombre_personne_minimum, m.quantite_restante,
                m.delai_minimum_jours, m.actif, t.libelle AS theme, r.libelle AS regime,
                (SELECT COUNT(*) FROM commande c WHERE c.menu_id = m.menu_id) AS commandes,
                (SELECT COUNT(*) FROM menu_plat mp WHERE mp.menu_id = m.menu_id) AS plats
            FROM menu m
            JOIN theme t ON t.theme_id = m.theme_id
            JOIN regime r ON r.regime_id = m.regime_id
            ORDER BY m.actif DESC, m.titre'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT m.*, (SELECT COUNT(*) FROM commande c WHERE c.menu_id = m.menu_id) AS commandes
            FROM menu m WHERE m.menu_id = :id'
        );
        $statement->execute(['id' => $id]);
        $menu = $statement->fetch();

        return $menu === false ? null : $menu;
    }

    public function platIds(int $menuId): array
    {
        $statement = $this->pdo()->prepare('SELECT plat_id FROM menu_plat WHERE menu_id = :id');
        $statement->execute(['id' => $menuId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public function images(int $menuId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT image_id, chemin, texte_alternatif, ordre FROM menu_image WHERE menu_id = :id ORDER BY ordre, image_id'
        );
        $statement->execute(['id' => $menuId]);

        return $statement->fetchAll();
    }

    public function save(?int $id, array $data, array $images): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $id = $id === null ? $this->insert($data) : $this->update($id, $data);
            $this->syncPlats($id, $data['plats']);
            $removed = $this->syncImages($id, $images);
            $pdo->commit();

            return ['id' => $id, 'removed' => $removed];
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function setActive(int $id, bool $active): bool
    {
        if ($this->find($id) === null) {
            return false;
        }
        $this->pdo()->prepare('UPDATE menu SET actif = :actif WHERE menu_id = :id')
            ->execute(['actif' => (int) $active, 'id' => $id]);

        return true;
    }

    public function delete(int $id): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $lock = $pdo->prepare('SELECT menu_id FROM menu WHERE menu_id = :id FOR UPDATE');
            $lock->execute(['id' => $id]);
            if ($lock->fetchColumn() === false) {
                $pdo->rollBack();
                return ['error' => 'notfound'];
            }

            $orders = $pdo->prepare('SELECT COUNT(*) FROM commande WHERE menu_id = :id');
            $orders->execute(['id' => $id]);
            $count = (int) $orders->fetchColumn();
            if ($count > 0) {
                $pdo->rollBack();
                return ['error' => 'ordered', 'count' => $count];
            }

            $paths = array_column($this->images($id), 'chemin');
            $pdo->prepare('DELETE FROM menu WHERE menu_id = :id')->execute(['id' => $id]);
            $pdo->commit();

            return ['paths' => $paths];
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (($exception->errorInfo[1] ?? 0) === 1451) {
                return ['error' => 'ordered', 'count' => 0];
            }
            throw $exception;
        }
    }

    private function insert(array $data): int
    {
        $this->pdo()->prepare(
            'INSERT INTO menu (titre, description, conditions, nombre_personne_minimum, prix_par_personne,
                quantite_restante, delai_minimum_jours, regime_id, theme_id)
            VALUES (:titre, :description, :conditions, :minimum, :prix, :stock, :delai, :regime, :theme)'
        )->execute($this->values($data));

        return (int) $this->pdo()->lastInsertId();
    }

    private function update(int $id, array $data): int
    {
        $this->pdo()->prepare(
            'UPDATE menu SET titre = :titre, description = :description, conditions = :conditions,
                nombre_personne_minimum = :minimum, prix_par_personne = :prix, quantite_restante = :stock,
                delai_minimum_jours = :delai, regime_id = :regime, theme_id = :theme
            WHERE menu_id = :id'
        )->execute($this->values($data) + ['id' => $id]);

        return $id;
    }

    private function values(array $data): array
    {
        return [
            'titre' => $data['titre'],
            'description' => $data['description'],
            'conditions' => $data['conditions'],
            'minimum' => $data['nombre_personne_minimum'],
            'prix' => $data['prix_par_personne'],
            'stock' => $data['quantite_restante'],
            'delai' => $data['delai_minimum_jours'],
            'regime' => $data['regime_id'],
            'theme' => $data['theme_id'],
        ];
    }

    private function syncPlats(int $menuId, array $platIds): void
    {
        $this->pdo()->prepare('DELETE FROM menu_plat WHERE menu_id = :id')->execute(['id' => $menuId]);
        $insert = $this->pdo()->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (:menu, :plat)');
        foreach ($platIds as $platId) {
            $insert->execute(['menu' => $menuId, 'plat' => $platId]);
        }
    }

    private function syncImages(int $menuId, array $images): array
    {
        $pdo = $this->pdo();
        $removed = [];

        $select = $pdo->prepare('SELECT chemin FROM menu_image WHERE image_id = :image AND menu_id = :menu');
        $delete = $pdo->prepare('DELETE FROM menu_image WHERE image_id = :image AND menu_id = :menu');
        foreach ($images['delete'] as $imageId) {
            $select->execute(['image' => $imageId, 'menu' => $menuId]);
            $path = $select->fetchColumn();
            if ($path !== false) {
                $removed[] = (string) $path;
                $delete->execute(['image' => $imageId, 'menu' => $menuId]);
            }
        }

        $update = $pdo->prepare(
            'UPDATE menu_image SET texte_alternatif = :alt, ordre = :ordre WHERE image_id = :image AND menu_id = :menu'
        );
        foreach ($images['keep'] as $imageId => $image) {
            $update->execute(['alt' => $image['alt'], 'ordre' => $image['ordre'], 'image' => $imageId, 'menu' => $menuId]);
        }

        $insert = $pdo->prepare(
            'INSERT INTO menu_image (menu_id, chemin, texte_alternatif, ordre) VALUES (:menu, :chemin, :alt, :ordre)'
        );
        foreach ($images['new'] as $image) {
            $insert->execute(['menu' => $menuId, 'chemin' => $image['path'], 'alt' => $image['alt'], 'ordre' => $image['ordre']]);
        }

        return $removed;
    }
}

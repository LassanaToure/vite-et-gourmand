<?php
declare(strict_types=1);

final class PlatAdminModel extends Model
{
    public function listAll(): array
    {
        return $this->pdo()->query(
            "SELECT p.plat_id, p.titre_plat, p.categorie,
                (SELECT GROUP_CONCAT(a.libelle ORDER BY a.libelle SEPARATOR ', ')
                    FROM plat_allergene pa JOIN allergene a ON a.allergene_id = pa.allergene_id
                    WHERE pa.plat_id = p.plat_id) AS allergenes,
                (SELECT GROUP_CONCAT(m.titre ORDER BY m.titre SEPARATOR ', ')
                    FROM menu_plat mp JOIN menu m ON m.menu_id = mp.menu_id
                    WHERE mp.plat_id = p.plat_id) AS menus
            FROM plat p
            ORDER BY FIELD(p.categorie, 'entree', 'plat', 'dessert'), p.titre_plat"
        )->fetchAll();
    }

    public function forSelection(): array
    {
        return $this->pdo()->query(
            "SELECT plat_id, titre_plat, categorie FROM plat
            ORDER BY FIELD(categorie, 'entree', 'plat', 'dessert'), titre_plat"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo()->prepare('SELECT plat_id, titre_plat, categorie FROM plat WHERE plat_id = :id');
        $statement->execute(['id' => $id]);
        $plat = $statement->fetch();
        if ($plat === false) {
            return null;
        }

        $plat['allergenes'] = $this->ids('SELECT allergene_id FROM plat_allergene WHERE plat_id = :id', $id);
        $plat['menus'] = $this->ids('SELECT menu_id FROM menu_plat WHERE plat_id = :id', $id);

        return $plat;
    }

    public function save(?int $id, array $data): int
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            if ($id === null) {
                $pdo->prepare('INSERT INTO plat (titre_plat, categorie) VALUES (:titre, :categorie)')
                    ->execute(['titre' => $data['titre_plat'], 'categorie' => $data['categorie']]);
                $id = (int) $pdo->lastInsertId();
            } else {
                $pdo->prepare('UPDATE plat SET titre_plat = :titre, categorie = :categorie WHERE plat_id = :id')
                    ->execute(['titre' => $data['titre_plat'], 'categorie' => $data['categorie'], 'id' => $id]);
            }

            $this->sync('plat_allergene', 'allergene_id', $id, $data['allergenes']);
            $this->sync('menu_plat', 'menu_id', $id, $data['menus']);
            $pdo->commit();

            return $id;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $id): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $lock = $pdo->prepare('SELECT plat_id FROM plat WHERE plat_id = :id FOR UPDATE');
            $lock->execute(['id' => $id]);
            if ($lock->fetchColumn() === false) {
                $pdo->rollBack();
                return ['error' => 'notfound'];
            }

            $menus = $pdo->prepare(
                'SELECT m.titre FROM menu_plat mp JOIN menu m ON m.menu_id = mp.menu_id WHERE mp.plat_id = :id ORDER BY m.titre'
            );
            $menus->execute(['id' => $id]);
            $titles = $menus->fetchAll(PDO::FETCH_COLUMN);
            if ($titles !== []) {
                $pdo->rollBack();
                return ['error' => 'attached', 'menus' => $titles];
            }

            $pdo->prepare('DELETE FROM plat WHERE plat_id = :id')->execute(['id' => $id]);
            $pdo->commit();

            return ['ok' => true];
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function ids(string $sql, int $id): array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['id' => $id]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    private function sync(string $table, string $column, int $platId, array $ids): void
    {
        $allowed = ['plat_allergene' => 'allergene_id', 'menu_plat' => 'menu_id'];
        if (($allowed[$table] ?? null) !== $column) {
            throw new InvalidArgumentException('Liaison inconnue.');
        }

        $this->pdo()->prepare('DELETE FROM ' . $table . ' WHERE plat_id = :id')->execute(['id' => $platId]);
        $insert = $this->pdo()->prepare('INSERT INTO ' . $table . ' (plat_id, ' . $column . ') VALUES (:plat, :cible)');
        foreach ($ids as $id) {
            $insert->execute(['plat' => $platId, 'cible' => $id]);
        }
    }
}

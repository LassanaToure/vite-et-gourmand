<?php
declare(strict_types=1);

final class PlatModel extends Model
{
    public function forMenu(int $menuId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT p.plat_id, p.titre_plat, p.categorie, a.libelle AS allergene
            FROM menu_plat mp
            JOIN plat p ON p.plat_id = mp.plat_id
            LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
            LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
            WHERE mp.menu_id = :menu
            ORDER BY p.titre_plat, a.libelle'
        );
        $statement->execute(['menu' => $menuId]);

        $groups = ['entree' => [], 'plat' => [], 'dessert' => []];
        foreach ($statement->fetchAll() as $row) {
            $category = $row['categorie'];
            $id = $row['plat_id'];
            if (!isset($groups[$category][$id])) {
                $groups[$category][$id] = ['titre' => $row['titre_plat'], 'allergenes' => []];
            }
            if ($row['allergene'] !== null) {
                $groups[$category][$id]['allergenes'][] = $row['allergene'];
            }
        }

        return array_map('array_values', $groups);
    }
}

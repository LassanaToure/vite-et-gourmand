<?php
declare(strict_types=1);

final class MenuImageModel extends Model
{
    public function forMenu(int $menuId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT chemin, texte_alternatif
            FROM menu_image
            WHERE menu_id = :menu
            ORDER BY ordre, image_id'
        );
        $statement->execute(['menu' => $menuId]);

        return $statement->fetchAll();
    }
}

<?php
declare(strict_types=1);

final class MenuModel extends Model
{
    private const FILTERS = [
        'prix_max' => 'm.prix_par_personne <= :prix_max',
        'fourchette_min' => 'm.prix_par_personne >= :fourchette_min',
        'fourchette_max' => 'm.prix_par_personne <= :fourchette_max',
        'theme' => 'm.theme_id = :theme',
        'regime' => 'm.regime_id = :regime',
        'personnes' => 'm.nombre_personne_minimum <= :personnes',
    ];

    private const COLUMNS = 'm.menu_id, m.titre, m.description, m.conditions, m.nombre_personne_minimum,
        m.prix_par_personne, m.quantite_restante, m.delai_minimum_jours, m.actif, t.libelle AS theme, r.libelle AS regime';

    private const JOINS = 'FROM menu m
        JOIN theme t ON t.theme_id = m.theme_id
        JOIN regime r ON r.regime_id = m.regime_id';

    private const MAIN_IMAGE = '(SELECT i.%s FROM menu_image i WHERE i.menu_id = m.menu_id ORDER BY i.ordre, i.image_id LIMIT 1)';

    public function search(array $filters): array
    {
        $conditions = ['m.actif = 1'];
        $params = [];
        foreach (self::FILTERS as $key => $fragment) {
            if (isset($filters[$key])) {
                $conditions[] = $fragment;
                $params[$key] = $filters[$key];
            }
        }

        $sql = 'SELECT ' . self::COLUMNS . ',
            ' . sprintf(self::MAIN_IMAGE, 'chemin') . ' AS image_chemin,
            ' . sprintf(self::MAIN_IMAGE, 'texte_alternatif') . ' AS image_alt
            ' . self::JOINS
            . ($conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions))
            . ' ORDER BY m.prix_par_personne, m.titre';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findActive(int $id): ?array
    {
        $menu = $this->find($id);

        return $menu !== null && (bool) $menu['actif'] ? $menu : null;
    }

    public function find(int $id, bool $lock = false): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT ' . self::COLUMNS . ' ' . self::JOINS . ' WHERE m.menu_id = :id' . ($lock ? ' FOR UPDATE OF m' : '')
        );
        $statement->execute(['id' => $id]);
        $menu = $statement->fetch();

        return $menu === false ? null : $menu;
    }
}

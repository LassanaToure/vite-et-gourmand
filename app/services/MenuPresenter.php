<?php
declare(strict_types=1);

final class MenuPresenter
{
    public static function card(array $menu): array
    {
        $image = $menu['image_chemin'] ?? null;

        return [
            'id' => (int) $menu['menu_id'],
            'titre' => $menu['titre'],
            'description' => $menu['description'],
            'theme' => $menu['theme'],
            'regime' => $menu['regime'],
            'prix_par_personne' => (float) $menu['prix_par_personne'],
            'prix_affiche' => price((float) $menu['prix_par_personne']),
            'nombre_personne_minimum' => (int) $menu['nombre_personne_minimum'],
            'quantite_restante' => (int) $menu['quantite_restante'],
            'image_url' => $image === null ? '' : asset($image),
            'image_alt' => $menu['image_alt'] ?? '',
            'url' => url('/menus/' . (int) $menu['menu_id']),
        ];
    }

    public static function cards(array $menus): array
    {
        return array_map([self::class, 'card'], $menus);
    }
}

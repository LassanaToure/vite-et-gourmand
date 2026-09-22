<?php
declare(strict_types=1);

final class MenuController
{
    public static function index(array $params, array $query): array
    {
        $filters = MenuFilters::fromQuery($query);
        $references = new ReferenceModel();

        return [
            'vars' => [
                'menus' => MenuPresenter::cards((new MenuModel())->search($filters)),
                'themes' => $references->themes(),
                'regimes' => $references->regimes(),
                'filters' => $filters,
            ],
        ];
    }

    public static function show(array $params, array $query): ?array
    {
        $id = (int) $params['id'];
        $menu = (new MenuModel())->findActive($id);

        if ($menu === null) {
            return null;
        }

        return [
            'title' => $menu['titre'],
            'description' => mb_strimwidth($menu['description'], 0, 155, '…'),
            'vars' => [
                'menu' => $menu,
                'images' => (new MenuImageModel())->forMenu($id),
                'plats' => (new PlatModel())->forMenu($id),
            ],
        ];
    }
}

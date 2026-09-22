<?php
declare(strict_types=1);

final class AdminPlatController
{
    public static function index(array $params, array $query): array
    {
        return ['vars' => ['plats' => (new PlatAdminModel())->listAll()]];
    }

    public static function form(array $params, array $query): ?array
    {
        $model = new PlatAdminModel();
        $id = isset($params['id']) ? (int) $params['id'] : null;
        $plat = $id === null ? null : $model->find($id);
        if ($id !== null && $plat === null) {
            return null;
        }

        $references = self::references();
        $values = $plat === null
            ? ['titre_plat' => '', 'categorie' => '', 'allergenes' => [], 'menus' => []]
            : ['titre_plat' => $plat['titre_plat'], 'categorie' => $plat['categorie'], 'allergenes' => $plat['allergenes'], 'menus' => $plat['menus']];
        $errors = [];

        if (is_post()) {
            $values = [
                'titre_plat' => input_string($_POST, 'titre_plat'),
                'categorie' => input_string($_POST, 'categorie'),
                'allergenes' => input_array($_POST, 'allergenes'),
                'menus' => input_array($_POST, 'menus'),
            ];
            [$errors, $clean] = PlatFormValidator::validate($values, $references['ids']);

            if ($errors === []) {
                $savedId = $model->save($id, $clean);
                Session::flash('success', $id === null ? 'Le plat a été créé.' : 'Le plat a été enregistré.');

                return ['redirect' => '/admin/plats/' . $savedId . '/modifier'];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['plat' => $plat, 'values' => $values, 'errors' => $errors, 'references' => $references],
        ];
    }

    public static function delete(array $params, array $query): ?array
    {
        $id = (int) $params['id'];
        if (!is_post()) {
            return ['redirect' => '/admin/plats/' . $id . '/modifier'];
        }

        $result = (new PlatAdminModel())->delete($id);
        if (($result['error'] ?? null) === 'notfound') {
            return null;
        }
        if (isset($result['error'])) {
            Session::flash('error', 'Ce plat est rattaché à : ' . implode(', ', $result['menus']) . '. Retirez-le d\'abord de ces menus pour le supprimer.');

            return ['redirect' => '/admin/plats/' . $id . '/modifier'];
        }

        Session::flash('success', 'Le plat a été supprimé.');

        return ['redirect' => '/admin/plats'];
    }

    private static function references(): array
    {
        $allergenes = (new ReferenceModel())->allergenes();
        $menus = (new MenuAdminModel())->listAll();

        return [
            'allergenes' => $allergenes,
            'menus' => $menus,
            'ids' => [
                'allergenes' => array_map('intval', array_column($allergenes, 'id')),
                'menus' => array_map('intval', array_column($menus, 'menu_id')),
            ],
        ];
    }
}

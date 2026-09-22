<?php
declare(strict_types=1);

final class AdminMenuController
{
    private const FIELDS = ['titre', 'description', 'conditions', 'theme_id', 'regime_id', 'nombre_personne_minimum', 'prix_par_personne', 'quantite_restante', 'delai_minimum_jours'];

    public static function index(array $params, array $query): array
    {
        return ['vars' => ['menus' => (new MenuAdminModel())->listAll()]];
    }

    public static function form(array $params, array $query): ?array
    {
        $model = new MenuAdminModel();
        $id = isset($params['id']) ? (int) $params['id'] : null;
        $menu = $id === null ? null : $model->find($id);
        if ($id !== null && $menu === null) {
            return null;
        }

        $references = self::references();
        $existing = $id === null ? [] : $model->images($id);
        $values = $menu === null ? self::blank() : self::fromMenu($menu, $model->platIds($id));
        $errors = [];

        if (is_post()) {
            $input = self::posted();
            $values = $input;
            [$errors, $clean] = MenuFormValidator::validate($input, $references['ids']);
            [$imageErrors, $images] = self::images($existing);
            $errors += $imageErrors;

            if ($errors === []) {
                return self::persist($model, $id, $clean, $images);
            }
            foreach ($existing as $index => $image) {
                $row = input_array(input_array($_POST, 'images'), (string) $image['image_id']);
                $existing[$index]['texte_alternatif'] = is_string($row['alt'] ?? null) ? trim($row['alt']) : $image['texte_alternatif'];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => [
                'menu' => $menu,
                'values' => $values,
                'errors' => $errors,
                'images' => $existing,
                'references' => $references,
                'slots' => ImageUploader::MAX_IMAGES - count($existing),
            ],
        ];
    }

    public static function archive(array $params, array $query): ?array
    {
        return self::toggle($params, false, 'Le menu est archivé : il n\'est plus visible sur le site public.');
    }

    public static function restore(array $params, array $query): ?array
    {
        return self::toggle($params, true, 'Le menu est de nouveau visible sur le site public.');
    }

    public static function delete(array $params, array $query): ?array
    {
        $id = (int) $params['id'];
        if (!is_post()) {
            return ['redirect' => '/admin/menus/' . $id . '/modifier'];
        }

        $result = (new MenuAdminModel())->delete($id);
        if (($result['error'] ?? null) === 'notfound') {
            return null;
        }
        if (isset($result['error'])) {
            $count = (int) ($result['count'] ?? 0);
            Session::flash('error', ($count > 0 ? $count . ' commande' . ($count > 1 ? 's utilisent' : ' utilise') : 'Des commandes utilisent') . ' ce menu : il ne peut pas être supprimé. Archivez-le à la place.');

            return ['redirect' => '/admin/menus/' . $id . '/modifier'];
        }

        foreach ($result['paths'] as $path) {
            ImageUploader::remove($path);
        }
        Session::flash('success', 'Le menu a été supprimé définitivement.');

        return ['redirect' => '/admin/menus'];
    }

    private static function toggle(array $params, bool $active, string $message): ?array
    {
        $id = (int) $params['id'];
        if (!is_post()) {
            return ['redirect' => '/admin/menus/' . $id . '/modifier'];
        }
        if (!(new MenuAdminModel())->setActive($id, $active)) {
            return null;
        }
        Session::flash('success', $message);

        return ['redirect' => '/admin/menus/' . $id . '/modifier'];
    }

    private static function persist(MenuAdminModel $model, ?int $id, array $clean, array $images): array
    {
        $stored = [];

        try {
            foreach ($images['new'] as $image) {
                $stored[] = ['path' => ImageUploader::store($image['file']), 'alt' => $image['alt'], 'ordre' => $image['ordre']];
            }
            $saved = $model->save($id, $clean, ['keep' => $images['keep'], 'delete' => $images['delete'], 'new' => $stored]);
        } catch (Throwable $exception) {
            foreach ($stored as $image) {
                ImageUploader::remove($image['path']);
            }
            throw $exception;
        }

        foreach ($saved['removed'] as $path) {
            ImageUploader::remove($path);
        }
        Session::flash('success', $id === null ? 'Le menu a été créé.' : 'Le menu a été enregistré.');

        return ['redirect' => '/admin/menus/' . $saved['id'] . '/modifier'];
    }

    private static function images(array $existing): array
    {
        $errors = [];
        $keep = [];
        $delete = [];
        $new = [];
        $posted = input_array($_POST, 'images');
        $highest = 0;

        foreach ($existing as $image) {
            $id = (int) $image['image_id'];
            $row = input_array($posted, (string) $id);

            if (($row['delete'] ?? '') === '1') {
                $delete[] = $id;
                continue;
            }

            $alt = is_string($row['alt'] ?? null) ? trim($row['alt']) : '';
            $order = is_string($row['ordre'] ?? null) && preg_match('/^\d{1,2}$/', $row['ordre']) === 1 ? (int) $row['ordre'] : (int) $image['ordre'];
            if (mb_strlen($alt) < 3 || mb_strlen($alt) > 255) {
                $errors['image-' . $id] = 'Le texte alternatif de chaque image est obligatoire (3 à 255 caractères).';
            }
            $keep[$id] = ['alt' => $alt, 'ordre' => $order];
            $highest = max($highest, $order);
        }

        $alts = input_array($_POST, 'new_alt');
        foreach (ImageUploader::files('new_images') as $index => $file) {
            if (ImageUploader::isEmpty($file)) {
                continue;
            }

            $alt = is_string($alts[$index] ?? null) ? trim($alts[$index]) : '';
            $problem = ImageUploader::validate($file);
            if ($problem !== null) {
                $errors['new-' . $index] = $problem;
            } elseif (mb_strlen($alt) < 3 || mb_strlen($alt) > 255) {
                $errors['new-' . $index] = 'Renseignez le texte alternatif de la nouvelle image (3 à 255 caractères).';
            } else {
                $new[] = ['file' => $file, 'alt' => $alt, 'ordre' => ++$highest];
            }
        }

        if (count($keep) + count($new) > ImageUploader::MAX_IMAGES) {
            $errors['images'] = 'Un menu ne peut pas avoir plus de ' . ImageUploader::MAX_IMAGES . ' images.';
        }

        return [$errors, ['keep' => $keep, 'delete' => $delete, 'new' => $new]];
    }

    private static function references(): array
    {
        $referenceModel = new ReferenceModel();
        $themes = $referenceModel->themes();
        $regimes = $referenceModel->regimes();
        $plats = (new PlatAdminModel())->forSelection();

        return [
            'themes' => $themes,
            'regimes' => $regimes,
            'plats' => $plats,
            'ids' => [
                'theme_id' => array_map('intval', array_column($themes, 'id')),
                'regime_id' => array_map('intval', array_column($regimes, 'id')),
                'plats' => array_map('intval', array_column($plats, 'plat_id')),
            ],
        ];
    }

    private static function posted(): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            $values[$field] = input_string($_POST, $field);
        }
        $values['plats'] = input_array($_POST, 'plats');

        return $values;
    }

    private static function blank(): array
    {
        return [
            'titre' => '', 'description' => '', 'conditions' => '', 'theme_id' => '', 'regime_id' => '',
            'nombre_personne_minimum' => '', 'prix_par_personne' => '', 'quantite_restante' => '0',
            'delai_minimum_jours' => '3', 'plats' => [],
        ];
    }

    private static function fromMenu(array $menu, array $platIds): array
    {
        return [
            'titre' => $menu['titre'],
            'description' => $menu['description'],
            'conditions' => $menu['conditions'],
            'theme_id' => (string) $menu['theme_id'],
            'regime_id' => (string) $menu['regime_id'],
            'nombre_personne_minimum' => (string) $menu['nombre_personne_minimum'],
            'prix_par_personne' => rtrim(rtrim((string) $menu['prix_par_personne'], '0'), '.'),
            'quantite_restante' => (string) $menu['quantite_restante'],
            'delai_minimum_jours' => (string) $menu['delai_minimum_jours'],
            'plats' => $platIds,
        ];
    }
}

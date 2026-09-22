<div class="admin-head admin-head--split">
    <div>
        <h1>Menus</h1>
        <p>Gérez la carte : un menu déjà commandé ne peut pas être supprimé, il s'archive.</p>
    </div>
    <a class="btn" href="<?= e(url('/admin/menus/nouveau')) ?>">Nouveau menu</a>
</div>

<div class="table-wrap">
    <table class="admin-table">
        <caption class="sr-only">Liste des menus</caption>
        <thead>
            <tr>
                <th scope="col">Menu</th>
                <th scope="col">Thème / régime</th>
                <th scope="col">Prix / pers.</th>
                <th scope="col">Minimum</th>
                <th scope="col">Stock</th>
                <th scope="col">Délai</th>
                <th scope="col">Commandes</th>
                <th scope="col">État</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $menu): ?>
                <tr<?= (bool) $menu['actif'] ? '' : ' class="admin-table__muted"' ?>>
                    <th scope="row"><a href="<?= e(url('/admin/menus/' . (int) $menu['menu_id'] . '/modifier')) ?>"><?= e($menu['titre']) ?></a></th>
                    <td><?= e($menu['theme']) ?> · <?= e($menu['regime']) ?></td>
                    <td><?= e(price((float) $menu['prix_par_personne'])) ?></td>
                    <td><?= (int) $menu['nombre_personne_minimum'] ?></td>
                    <td><?= (int) $menu['quantite_restante'] ?></td>
                    <td><?= (int) $menu['delai_minimum_jours'] ?> j</td>
                    <td><?= (int) $menu['commandes'] ?></td>
                    <td><span class="status status--<?= (bool) $menu['actif'] ? 'done' : 'cancelled' ?>"><?= (bool) $menu['actif'] ? 'Visible' : 'Archivé' ?></span></td>
                    <td class="admin-table__actions"><a class="btn btn--small btn--outline" href="<?= e(url('/admin/menus/' . (int) $menu['menu_id'] . '/modifier')) ?>">Modifier</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $categories = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert']; ?>
<div class="admin-head admin-head--split">
    <div>
        <h1>Plats</h1>
        <p>Un plat peut être rattaché à plusieurs menus. Un plat rattaché à un menu ne peut pas être supprimé.</p>
    </div>
    <a class="btn" href="<?= e(url('/admin/plats/nouveau')) ?>">Nouveau plat</a>
</div>

<div class="table-wrap">
    <table class="admin-table">
        <caption class="sr-only">Liste des plats</caption>
        <thead>
            <tr>
                <th scope="col">Plat</th>
                <th scope="col">Type</th>
                <th scope="col">Allergènes</th>
                <th scope="col">Menus</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plats as $plat): ?>
                <tr>
                    <th scope="row"><a href="<?= e(url('/admin/plats/' . (int) $plat['plat_id'] . '/modifier')) ?>"><?= e($plat['titre_plat']) ?></a></th>
                    <td><?= e($categories[$plat['categorie']]) ?></td>
                    <td><?= e($plat['allergenes'] ?? 'Aucun') ?></td>
                    <td><?= e($plat['menus'] ?? 'Aucun') ?></td>
                    <td class="admin-table__actions"><a class="btn btn--small btn--outline" href="<?= e(url('/admin/plats/' . (int) $plat['plat_id'] . '/modifier')) ?>">Modifier</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

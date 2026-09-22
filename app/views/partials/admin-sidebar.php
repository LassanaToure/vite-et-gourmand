<?php
$items = [
    ['label' => 'Commandes', 'path' => '/admin/commandes'],
    ['label' => 'Menus', 'path' => '/admin/menus'],
    ['label' => 'Plats', 'path' => '/admin/plats'],
    ['label' => 'Horaires', 'path' => '/admin/horaires'],
    ['label' => 'Avis clients', 'path' => '/admin/avis'],
];
if (Auth::hasRole('administrateur')) {
    $items[] = ['label' => 'Employés', 'path' => '/admin/employes'];
    $items[] = ['label' => 'Statistiques', 'path' => '/admin/stats'];
}
?>
<nav class="admin-sidebar" aria-label="Navigation du back-office">
    <ul class="admin-sidebar__list">
        <?php foreach ($items as $item): ?>
            <li>
                <a class="admin-sidebar__link" href="<?= e(url($item['path'])) ?>"<?= is_current($item['path']) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

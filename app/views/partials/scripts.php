<?php
$pageScripts = [
    'menus' => ['menu-filters.js'],
    'menu-detail' => ['menu-gallery.js'],
    'login' => ['password-toggle.js'],
    'register' => ['password-toggle.js'],
    'reset-password' => ['password-toggle.js'],
    'order' => ['order-form.js'],
    'account' => ['password-toggle.js'],
    'my-orders' => ['tabs.js'],
    'admin/orders' => ['admin-orders.js'],
    'admin/order' => ['cancel-dialog.js'],
    'admin/order-cancel' => ['cancel-dialog.js'],
    'admin/reviews' => ['tabs.js'],
    'admin/employee-form' => ['password-toggle.js'],
];
$scripts = array_merge(['nav.js'], $pageScripts[$page] ?? []);
foreach ($scripts as $script): ?>
<script src="<?= e(asset('js/' . $script)) ?>" defer></script>
<?php endforeach; ?>

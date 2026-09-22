<!DOCTYPE html>
<html lang="fr">
<head>
<?php partial('head', compact('title', 'description', 'page')) ?>
</head>
<body class="admin-body">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<div class="admin">
    <header class="admin-topbar">
        <a class="admin-topbar__brand" href="<?= e(url('/admin/commandes')) ?>"><?= e(site()['name']) ?> <span>Back-office</span></a>
        <div class="admin-topbar__tools">
            <a class="admin-topbar__link" href="<?= e(url('/')) ?>">Voir le site</a>
            <span class="admin-topbar__user"><?= e(current_user()['prenom'] ?? current_user()['email']) ?> <small>(<?= current_user()['role'] === 'administrateur' ? 'administrateur' : 'employé' ?>)</small></span>
            <form method="post" action="<?= e(url('/deconnexion')) ?>">
                <?= csrf_field() ?>
                <button class="admin-topbar__link admin-topbar__button" type="submit">Déconnexion</button>
            </form>
        </div>
    </header>
    <div class="admin__body">
        <?php partial('admin-sidebar') ?>
        <main class="admin-main" id="contenu" tabindex="-1">
            <?php partial('flash') ?>
<?= $content ?>
        </main>
    </div>
</div>
<?php partial('scripts', compact('page')) ?>
</body>
</html>

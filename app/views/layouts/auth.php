<!DOCTYPE html>
<html lang="fr">
<head>
<?php partial('head', compact('title', 'description', 'page')) ?>
</head>
<body class="page-auth">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<main class="auth" id="contenu" tabindex="-1">
    <div class="auth-card">
        <a class="auth-card__logo" href="<?= e(url('/')) ?>"><?= e(site()['name']) ?></a>
<?php partial('flash') ?>
<?= $content ?>
    </div>
</main>
<?php partial('scripts', compact('page')) ?>
</body>
</html>

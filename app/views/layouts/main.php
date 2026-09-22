<!DOCTYPE html>
<html lang="fr">
<head>
<?php partial('head', compact('title', 'description', 'page')) ?>
</head>
<body class="page-<?= e($page) ?>">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<?php partial('header') ?>
<main id="contenu" tabindex="-1">
<?php partial('flash', ['wrap' => true]) ?>
<?= $content ?>
</main>
<?php partial('footer') ?>
<?php partial('scripts', compact('page')) ?>
</body>
</html>

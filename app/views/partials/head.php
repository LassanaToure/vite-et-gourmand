<?php $siteName = site()['name']; ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page === 'home' ? $siteName . ' | ' . $title : $title . ' | ' . $siteName) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="theme-color" content="#6E1E2C">
<link rel="icon" href="data:,">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&amp;display=swap">
<link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
<script<?= is_production() ? ' nonce="' . e(csp_nonce()) . '"' : '' ?>>document.documentElement.classList.add('js');</script>

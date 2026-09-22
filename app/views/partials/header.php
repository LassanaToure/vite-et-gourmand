<?php
$site = site();
$user = current_user();
$displayName = $user['prenom'] ?? $user['email'] ?? '';
?>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="site-logo" href="<?= e(url('/')) ?>"><?= e($site['name']) ?></a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
            <span class="sr-only">Menu</span>
            <?php icon('menu', 'icon icon--lg') ?>
        </button>
        <div class="site-nav" id="site-nav" data-nav>
            <nav aria-label="Navigation principale">
                <ul class="site-nav__list">
                    <?php foreach ($site['nav'] as $item): ?>
                        <li>
                            <a class="site-nav__link" href="<?= e(url($item['path'])) ?>"<?= is_current($item['path']) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <?php if ($user !== null): ?>
                <div class="account" data-account>
                    <button class="account__toggle" type="button" aria-expanded="false" aria-controls="account-menu" data-account-toggle>
                        <span class="account__avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($displayName, 0, 1))) ?></span>
                        <span class="account__name"><?= e($displayName) ?></span>
                        <?php icon('chevron', 'icon icon--sm') ?>
                    </button>
                    <ul class="account__menu" id="account-menu">
                        <?php foreach ($site['account_links'] as $link): ?>
                            <?php if (isset($link['roles']) && !in_array($user['role'], $link['roles'], true)) { continue; } ?>
                            <li><a class="account__link" href="<?= e(url($link['path'])) ?>"<?= is_current($link['path']) ? ' aria-current="page"' : '' ?>><?= e($link['label']) ?></a></li>
                        <?php endforeach; ?>
                        <li>
                            <form method="post" action="<?= e(url('/deconnexion')) ?>">
                                <?= csrf_field() ?>
                                <button class="account__link account__link--button" type="submit">Déconnexion</button>
                            </form>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="site-nav__actions">
                    <a class="site-nav__link" href="<?= e(url('/connexion')) ?>"<?= is_current('/connexion') ? ' aria-current="page"' : '' ?>>Connexion</a>
                    <a class="btn btn--small" href="<?= e(url('/inscription')) ?>">Créer un compte</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

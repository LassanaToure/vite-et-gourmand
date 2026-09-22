<?php
$card = $menu ?? [
    'titre' => '',
    'description' => '',
    'theme' => '',
    'regime' => '',
    'prix_affiche' => '',
    'nombre_personne_minimum' => '',
    'image_url' => '',
    'url' => '',
];
?>
<article class="menu-card" data-menu-card>
    <img class="menu-card__image" src="<?= e($card['image_url']) ?>" alt="" width="800" height="600" loading="lazy" data-field="image"<?= $card['image_url'] === '' ? ' hidden' : '' ?>>
    <div class="menu-card__body">
        <ul class="badges" aria-label="Caractéristiques">
            <li class="badge badge--theme" data-field="theme"><?= e($card['theme']) ?></li>
            <li class="badge badge--diet" data-field="regime"><?= e($card['regime']) ?></li>
        </ul>
        <h3 class="menu-card__title">
            <a class="menu-card__link" href="<?= e($card['url']) ?>" data-field="link"><span data-field="titre"><?= e($card['titre']) ?></span></a>
        </h3>
        <p class="menu-card__summary" data-field="description"><?= e($card['description']) ?></p>
        <p class="menu-card__meta">
            <span class="menu-card__price"><span data-field="prix"><?= e($card['prix_affiche']) ?></span> <span class="menu-card__unit">/ personne</span></span>
            <span class="menu-card__min">Dès <span data-field="minimum"><?= e((string) $card['nombre_personne_minimum']) ?></span> personnes</span>
        </p>
    </div>
</article>

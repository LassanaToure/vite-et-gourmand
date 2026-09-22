<?php
$stock = (int) $menu['quantite_restante'];
$orderUrl = url('/commande') . '?menuId=' . (int) $menu['menu_id'];
?>
<section class="section menu-detail" aria-labelledby="menu-title">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <ol>
                <li><a href="<?= e(url('/menus')) ?>">Nos menus</a></li>
                <li aria-current="page"><?= e($menu['titre']) ?></li>
            </ol>
        </nav>

        <div class="detail__grid">
            <?php if ($images !== []): ?>
                <div class="gallery" data-gallery>
                    <div class="gallery__main">
                        <img src="<?= e(asset($images[0]['chemin'])) ?>" alt="<?= e($images[0]['texte_alternatif']) ?>" width="800" height="600" data-gallery-main>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <ul class="gallery__thumbs list-reset">
                            <?php foreach ($images as $index => $image): ?>
                                <li>
                                    <button class="gallery__thumb" type="button" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Afficher l'image <?= $index + 1 ?>" data-src="<?= e(asset($image['chemin'])) ?>" data-alt="<?= e($image['texte_alternatif']) ?>">
                                        <img src="<?= e(asset($image['chemin'])) ?>" alt="" width="200" height="150">
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="detail__summary">
                <ul class="badges" aria-label="Caractéristiques">
                    <li class="badge badge--theme"><?= e($menu['theme']) ?></li>
                    <li class="badge badge--diet"><?= e($menu['regime']) ?></li>
                </ul>
                <h1 id="menu-title"><?= e($menu['titre']) ?></h1>
                <p class="lead"><?= e($menu['description']) ?></p>
                <p class="detail__price"><?= e(price((float) $menu['prix_par_personne'])) ?> <span>/ personne</span></p>
                <dl class="facts">
                    <div><dt>Minimum</dt><dd><?= (int) $menu['nombre_personne_minimum'] ?> personnes</dd></div>
                    <div><dt>Prix pour le minimum</dt><dd><?= e(price((float) $menu['prix_par_personne'] * (int) $menu['nombre_personne_minimum'])) ?></dd></div>
                    <div><dt>Disponibilité</dt><dd><?= $stock > 0 ? $stock . ' commande' . ($stock > 1 ? 's' : '') . ' restante' . ($stock > 1 ? 's' : '') : 'Menu complet' ?></dd></div>
                </dl>
                <aside class="conditions-note" aria-labelledby="conditions-title">
                    <h2 id="conditions-title">Conditions de ce menu</h2>
                    <p><?= e($menu['conditions']) ?></p>
                </aside>
                <a class="btn btn--block" href="<?= e($orderUrl) ?>">Commander ce menu <span aria-hidden="true">→</span></a>
            </div>
        </div>

        <section class="detail__section" aria-labelledby="composition-title">
            <h2 id="composition-title">Composition du menu</h2>
            <?php partial('dish-groups', ['plats' => $plats]) ?>
        </section>
    </div>
</section>

<?php
$path = url('/mes-commandes/' . (int) $order['commande_id']);
$pending = $order['statut'] === 'en_attente';
$notes = ['terminee' => 'Commande terminée, non modifiable.', 'annulee' => 'Commande annulée, non modifiable.'];
$lockedNote = $notes[$order['statut']] ?? 'Commande acceptée, non modifiable.';
$reviewLabels = ['en_attente' => 'En attente de validation', 'valide' => 'Publié', 'refuse' => 'Non publié'];
$rows = [
    'Date de la prestation' => format_date($order['date_prestation']),
    'Heure de livraison' => substr($order['heure_livraison'], 0, 5),
    'Nombre de personnes' => (string) (int) $order['nombre_personne'],
    'Adresse' => $order['adresse_prestation'] . ', ' . $order['code_postal_prestation'] . ' ' . $order['ville_prestation'],
];
$contact = [
    'Nom' => trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')),
    'Email' => $user['email'],
    'GSM de contact' => $order['telephone_contact'],
];
?>
<section class="section my-order" aria-labelledby="order-title">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <ol>
                <li><a href="<?= e(url('/mes-commandes')) ?>">Mes commandes</a></li>
                <li aria-current="page"><?= e($order['numero_commande']) ?></li>
            </ol>
        </nav>

        <div class="my-order__head">
            <div>
                <p class="eyebrow">Commande <?= e($order['numero_commande']) ?></p>
                <h1 id="order-title"><?= e($order['menu_titre']) ?></h1>
            </div>
            <?php partial('status-badge', ['code' => $order['statut']]) ?>
        </div>

        <div class="order-form__grid">
            <div class="order-form__main">
                <section class="card order-block" aria-labelledby="block-presta">
                    <h2 class="order-block__title" id="block-presta">Prestation</h2>
                    <dl class="identity">
                        <?php foreach ($rows as $label => $value): ?>
                            <div><dt><?= e($label) ?></dt><dd><?= e($value) ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                </section>

                <section class="card order-block" aria-labelledby="block-contact">
                    <h2 class="order-block__title" id="block-contact">Coordonnées</h2>
                    <dl class="identity">
                        <?php foreach ($contact as $label => $value): ?>
                            <div><dt><?= e($label) ?></dt><dd><?= e($value) ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                </section>

                <section aria-labelledby="block-menu">
                    <h2 class="order-block__title" id="block-menu">Composition du menu</h2>
                    <?php partial('dish-groups', ['plats' => $plats]) ?>
                </section>

                <section class="conditions-note" aria-labelledby="block-conditions">
                    <h2 id="block-conditions">Conditions de ce menu</h2>
                    <p><?= e($order['menu_conditions']) ?></p>
                </section>

                <?php if ($order['statut'] === 'annulee' && !empty($order['motif_annulation'])): ?>
                    <section class="card order-block" aria-labelledby="block-cancel">
                        <h2 class="order-block__title" id="block-cancel">Motif de l'annulation</h2>
                        <p><?= e($order['motif_annulation']) ?></p>
                    </section>
                <?php endif; ?>

                <?php if ($review !== null): ?>
                    <section class="card order-block" aria-labelledby="block-review">
                        <h2 class="order-block__title" id="block-review">Votre avis</h2>
                        <p class="review-note"><strong>Note : <?= (int) $review['note'] ?> sur 5</strong> <span class="badge badge--diet"><?= e($reviewLabels[$review['statut']] ?? $review['statut']) ?></span></p>
                        <p><?= e($review['description']) ?></p>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="order-form__side">
                <?php partial('price-summary', ['summary' => $summary]) ?>

                <div class="card order-actions">
                    <h2 class="order-actions__title">Actions</h2>
                    <?php if ($pending): ?>
                        <a class="btn btn--block" href="<?= e($path . '/modifier') ?>">Modifier ma commande</a>
                        <a class="btn btn--block btn--outline" href="<?= e($path . '/annuler') ?>">Annuler ma commande</a>
                    <?php else: ?>
                        <button class="btn btn--block btn--outline" type="button" disabled aria-disabled="true" title="<?= e($lockedNote) ?>">Modifier ma commande</button>
                        <button class="btn btn--block btn--outline" type="button" disabled aria-disabled="true" title="<?= e($lockedNote) ?>">Annuler ma commande</button>
                        <p class="order-actions__note"><?= e($lockedNote) ?></p>
                        <a class="btn btn--block" href="<?= e($path . '/suivi') ?>"><?= $order['statut'] === 'terminee' || $order['statut'] === 'annulee' ? 'Voir l\'historique' : 'Suivre ma commande' ?></a>
                    <?php endif; ?>
                    <?php if ($order['statut'] === 'terminee' && $review === null): ?>
                        <a class="btn btn--block" href="<?= e($path . '/avis') ?>">Donner mon avis</a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

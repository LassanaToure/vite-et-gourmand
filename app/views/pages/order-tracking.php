<?php
$path = url('/mes-commandes/' . (int) $order['commande_id']);
$statuses = order_statuses();
$contactSubject = 'Retour du matériel, commande ' . $order['numero_commande'];
?>
<section class="section" aria-labelledby="tracking-title">
    <div class="container container--narrow">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <ol>
                <li><a href="<?= e(url('/mes-commandes')) ?>">Mes commandes</a></li>
                <li><a href="<?= e($path) ?>"><?= e($order['numero_commande']) ?></a></li>
                <li aria-current="page">Suivi</li>
            </ol>
        </nav>

        <p class="eyebrow">Suivi de commande</p>
        <h1 id="tracking-title"><?= e($order['menu_titre']) ?></h1>
        <p class="lead">Commande <?= e($order['numero_commande']) ?>, statut actuel : <?php partial('status-badge', ['code' => $order['statut']]) ?></p>

        <?php if ($order['statut'] === 'attente_retour_materiel'): ?>
            <section class="alert-box" aria-labelledby="alert-title">
                <h2 id="alert-title">Matériel à nous restituer</h2>
                <p>Le matériel prêté (plats, couverts, contenants isothermes) doit nous être rendu propre et en bon état sous <strong>10 jours ouvrés</strong><?= $deadline !== null ? ', soit avant le <strong>' . e(format_date($deadline)) . '</strong>' : '' ?>. Au-delà, une pénalité forfaitaire de <strong>600,00 €</strong> sera facturée (article 5 des CGV).</p>
                <a class="btn" href="<?= e(url('/contact') . '?objet=' . rawurlencode($contactSubject)) ?>">Prendre contact</a>
            </section>
        <?php endif; ?>

        <ol class="timeline" aria-label="Historique de la commande">
            <?php foreach ($timeline['events'] as $event): ?>
                <li class="timeline__item timeline__item--<?= $event['current'] ? 'current' : 'done' ?>"<?= $event['current'] ? ' aria-current="step"' : '' ?>>
                    <p class="timeline__title"><?= e($event['title']) ?></p>
                    <p class="timeline__date"><?= e(format_datetime($event['at'])) ?></p>
                </li>
            <?php endforeach; ?>
            <?php foreach ($timeline['upcoming'] as $status): ?>
                <li class="timeline__item timeline__item--upcoming">
                    <p class="timeline__title"><?= e($statuses[$status]['event']) ?></p>
                    <p class="timeline__date">À venir</p>
                </li>
            <?php endforeach; ?>
        </ol>

        <?php if ($order['statut'] === 'annulee' && !empty($order['motif_annulation'])): ?>
            <p class="profile__note">Motif : <?= e($order['motif_annulation']) ?></p>
        <?php endif; ?>

        <div class="order-confirmation__actions">
            <a class="btn btn--outline" href="<?= e($path) ?>">Retour à la commande</a>
        </div>
    </div>
</section>

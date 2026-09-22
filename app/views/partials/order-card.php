<?php
$status = order_statuses()[$order['statut']];
$path = url('/mes-commandes/' . (int) $order['commande_id']);
$pending = $order['statut'] === 'en_attente';
$closed = $status['group'] === 'terminee';
$total = OrderPricing::cents($order['prix_menu']) + OrderPricing::cents($order['prix_livraison']);
?>
<article class="order-card" data-order-card data-group="<?= e($status['group']) ?>">
    <header class="order-card__head">
        <div>
            <p class="order-card__number"><?= e($order['numero_commande']) ?></p>
            <h3 class="order-card__title"><a href="<?= e($path) ?>"><?= e($order['menu_titre']) ?></a></h3>
        </div>
        <?php partial('status-badge', ['code' => $order['statut']]) ?>
    </header>
    <dl class="order-card__facts">
        <div><dt>Prestation</dt><dd><?= e(format_date($order['date_prestation'])) ?></dd></div>
        <div><dt>Personnes</dt><dd><?= (int) $order['nombre_personne'] ?></dd></div>
        <div><dt>Total</dt><dd><?= e(money($total)) ?></dd></div>
    </dl>
    <div class="order-card__actions">
        <a class="btn btn--small" href="<?= e($path) ?>">Voir le détail</a>
        <?php if ($pending): ?>
            <a class="btn btn--small btn--outline" href="<?= e($path . '/modifier') ?>">Modifier</a>
            <a class="btn btn--small btn--outline" href="<?= e($path . '/annuler') ?>">Annuler</a>
        <?php else: ?>
            <a class="btn btn--small btn--outline" href="<?= e($path . '/suivi') ?>"><?= $closed ? 'Voir l\'historique' : 'Suivre ma commande' ?></a>
        <?php endif; ?>
        <?php if ($order['statut'] === 'terminee' && (int) $order['avis_donne'] === 0): ?>
            <a class="btn btn--small btn--outline" href="<?= e($path . '/avis') ?>">Donner mon avis</a>
        <?php endif; ?>
    </div>
</article>

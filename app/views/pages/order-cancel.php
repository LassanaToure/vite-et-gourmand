<?php $path = url('/mes-commandes/' . (int) $order['commande_id']); ?>
<section class="section" aria-labelledby="cancel-title">
    <div class="container container--narrow">
        <p class="eyebrow">Commande <?= e($order['numero_commande']) ?></p>
        <h1 id="cancel-title">Annuler cette commande ?</h1>
        <p class="lead">Vous êtes sur le point d'annuler votre commande de <strong><?= e($order['menu_titre']) ?></strong> pour le <?= e(format_date($order['date_prestation'])) ?>. Cette action est définitive.</p>

        <?php partial('price-summary', ['summary' => $summary]) ?>

        <form class="order-confirmation__actions" method="post" action="<?= e($path . '/annuler') ?>">
            <?= csrf_field() ?>
            <button class="btn" type="submit">Oui, annuler ma commande</button>
            <a class="btn btn--outline" href="<?= e($path) ?>">Conserver ma commande</a>
        </form>
    </div>
</section>

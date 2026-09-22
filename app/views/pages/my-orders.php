<section class="section my-orders" aria-labelledby="orders-title">
    <div class="container">
        <div class="page-intro">
            <p class="eyebrow">Espace personnel</p>
            <h1 id="orders-title">Mes commandes</h1>
            <p class="lead">Retrouvez vos commandes, modifiez-les tant qu'elles ne sont pas acceptées et suivez leur avancement.</p>
        </div>

        <?php if ($orders === []): ?>
            <?php partial('empty-orders') ?>
        <?php else: ?>
            <div class="tabs" role="tablist" aria-label="Filtrer mes commandes" data-tabs hidden>
                <button class="tabs__tab" type="button" role="tab" id="tab-all" aria-selected="true" aria-controls="orders-panel" data-filter="all">Toutes (<?= (int) $counts['all'] ?>)</button>
                <button class="tabs__tab" type="button" role="tab" id="tab-en_cours" aria-selected="false" aria-controls="orders-panel" tabindex="-1" data-filter="en_cours">En cours (<?= (int) $counts['en_cours'] ?>)</button>
                <button class="tabs__tab" type="button" role="tab" id="tab-terminee" aria-selected="false" aria-controls="orders-panel" tabindex="-1" data-filter="terminee">Terminées (<?= (int) $counts['terminee'] ?>)</button>
            </div>

            <div id="orders-panel" role="tabpanel" aria-labelledby="tab-all" tabindex="0" data-panel>
                <ul class="order-list list-reset">
                    <?php foreach ($orders as $order): ?>
                        <li data-tab-item data-group="<?= e(order_statuses()[$order['statut']]['group']) ?>">
                            <?php partial('order-card', ['order' => $order]) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="orders__empty" data-tab-empty hidden>Aucune commande dans cet onglet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

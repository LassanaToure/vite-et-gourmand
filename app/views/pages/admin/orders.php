<?php
$statuses = order_statuses();
$columns = ['id' => 'N°', 'client' => 'Client', 'menu' => 'Menu', 'date' => 'Prestation', 'people' => 'Pers.', 'total' => 'Total', 'status' => 'Statut'];
$order_index = array_flip(array_keys($statuses));
?>
<div class="admin-head">
    <h1>Commandes</h1>
    <p>Suivez les commandes, faites-les avancer étape par étape et contactez le client avant toute annulation.</p>
</div>

<div class="admin-toolbar" data-toolbar hidden>
    <div class="chips" role="group" aria-label="Filtrer par statut">
        <button class="chip" type="button" data-status-filter="all" aria-pressed="true">Toutes (<?= count($orders) ?>)</button>
        <?php foreach ($statuses as $code => $status): ?>
            <button class="chip" type="button" data-status-filter="<?= e($code) ?>" aria-pressed="false"><?= e($status['label']) ?> (<?= (int) $counts[$code] ?>)</button>
        <?php endforeach; ?>
    </div>
    <div class="admin-search">
        <label class="sr-only" for="client-search">Rechercher un client</label>
        <input class="input" type="search" id="client-search" placeholder="Rechercher un client (nom ou e-mail)" data-client-search autocomplete="off">
    </div>
</div>
<p class="admin-count" role="status" data-result-count></p>

<div class="table-wrap">
    <table class="admin-table" data-orders-table>
        <caption class="sr-only">Liste des commandes</caption>
        <thead>
            <tr>
                <?php foreach ($columns as $key => $label): ?>
                    <th scope="col" aria-sort="none"><button class="sort" type="button" data-sort-key="<?= e($key) ?>"><?= e($label) ?></button></th>
                <?php endforeach; ?>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <?php
                $client = trim(($order['prenom'] ?? '') . ' ' . ($order['nom'] ?? ''));
                $total = OrderPricing::cents($order['prix_menu']) + OrderPricing::cents($order['prix_livraison']);
                $next = OrderFlow::next($order);
                $quick = $next !== null && OrderFlow::extra($order) === null;
                $path = url('/admin/commandes/' . (int) $order['commande_id']);
                ?>
                <tr data-order-row
                    data-status="<?= e($order['statut']) ?>"
                    data-search="<?= e(mb_strtolower($client . ' ' . $order['email'] . ' ' . $order['numero_commande'])) ?>"
                    data-id="<?= (int) $order['commande_id'] ?>"
                    data-client="<?= e(mb_strtolower($client)) ?>"
                    data-menu="<?= e(mb_strtolower($order['menu_titre'])) ?>"
                    data-date="<?= e($order['date_prestation']) ?>"
                    data-people="<?= (int) $order['nombre_personne'] ?>"
                    data-total="<?= $total ?>"
                    data-status-order="<?= (int) $order_index[$order['statut']] ?>">
                    <th scope="row"><a href="<?= e($path) ?>"><?= e($order['numero_commande']) ?></a></th>
                    <td><?= e($client) ?><small class="admin-table__sub"><?= e($order['email']) ?></small></td>
                    <td><?= e($order['menu_titre']) ?></td>
                    <td><?= e(format_date($order['date_prestation'])) ?></td>
                    <td><?= (int) $order['nombre_personne'] ?></td>
                    <td><?= e(money($total)) ?></td>
                    <td><?php partial('status-badge', ['code' => $order['statut']]) ?></td>
                    <td class="admin-table__actions">
                        <a class="btn btn--small btn--outline" href="<?= e($path) ?>">Voir</a>
                        <?php if ($quick): ?>
                            <form method="post" action="<?= e($path . '/statut') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="from" value="<?= e($order['statut']) ?>">
                                <button class="btn btn--small" type="submit"><?= e(OrderFlow::actionLabel($next)) ?></button>
                            </form>
                        <?php elseif ($next !== null): ?>
                            <a class="btn btn--small" href="<?= e($path) ?>">Traiter</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="admin-empty" data-empty hidden>Aucune commande ne correspond à votre recherche.</p>

<div class="admin-head admin-head--split">
    <div>
        <h1>Statistiques</h1>
        <p>Données issues de MongoDB, alimentées à chaque création, modification, annulation ou changement de statut d'une commande.</p>
    </div>
    <form method="post" action="<?= e(url('/admin/stats/synchroniser')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn--outline" type="submit">Resynchroniser depuis MySQL</button>
    </form>
</div>

<?php partial('form-errors', ['errors' => $errors]) ?>

<form class="card stats-filters" method="get" action="<?= e(url('/admin/stats')) ?>">
    <div class="field">
        <label class="field__label" for="menu">Menu</label>
        <select class="input" id="menu" name="menu">
            <option value="">Tous les menus</option>
            <?php foreach ($report['menus'] ?? [] as $menu): ?>
                <option value="<?= (int) $menu['menu_id'] ?>"<?= $filters['menu'] === $menu['menu_id'] ? ' selected' : '' ?>><?= e($menu['titre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label class="field__label" for="du">Commandes passées du</label>
        <input class="input" type="date" id="du" name="du" value="<?= e($filters['du']) ?>">
    </div>
    <div class="field">
        <label class="field__label" for="au">au</label>
        <input class="input" type="date" id="au" name="au" value="<?= e($filters['au']) ?>">
    </div>
    <div class="stats-filters__actions">
        <button class="btn" type="submit">Appliquer</button>
        <a class="btn btn--outline" href="<?= e(url('/admin/stats')) ?>">Réinitialiser</a>
    </div>
</form>

<?php if ($report === null): ?>
    <p class="flash flash--error" role="alert">MongoDB est injoignable : les statistiques ne peuvent pas être affichées. Vérifiez le service puis actualisez la page.</p>
<?php else: ?>
    <?php $kpi = $report['kpi']; ?>
    <dl class="stats-kpis">
        <div class="card stats-kpi">
            <dt>Commandes</dt>
            <dd><?= (int) $kpi['commandes'] ?></dd>
        </div>
        <div class="card stats-kpi">
            <dt>Chiffre d'affaires</dt>
            <dd><?= e(money($kpi['ca_cents'])) ?></dd>
        </div>
        <div class="card stats-kpi">
            <dt>Menu le plus commandé</dt>
            <dd class="stats-kpi__text"><?= $kpi['top'] === null ? 'Aucun' : e($kpi['top']['titre']) ?></dd>
            <?php if ($kpi['top'] !== null): ?><dd class="stats-kpi__sub"><?= (int) $kpi['top']['commandes'] ?> commande(s)</dd><?php endif; ?>
        </div>
    </dl>
    <p class="admin-note">Le chiffre d'affaires (menu après remise + livraison) et les compteurs excluent les commandes annulées<?= $kpi['annulees'] > 0 ? ' (' . (int) $kpi['annulees'] . ' annulée(s) sur la sélection)' : '' ?>. La période porte sur la date de commande.</p>

    <?php if ($report['rows'] === []): ?>
        <p class="admin-empty">Aucune commande ne correspond à cette sélection.</p>
    <?php else: ?>
        <?php
        $orders = array_map(static fn (array $row): array => ['label' => $row['titre'], 'value' => $row['commandes'], 'text' => $row['commandes'] . ' commande' . ($row['commandes'] > 1 ? 's' : '')], $report['rows']);
        $revenue = array_map(static fn (array $row): array => ['label' => $row['titre'], 'value' => $row['ca_cents'], 'text' => money($row['ca_cents'])], $report['rows']);
        usort($revenue, static fn (array $a, array $b): int => $b['value'] <=> $a['value']);
        ?>
        <div class="stats-charts">
            <?php partial('stats-chart', ['id' => 'chart-orders', 'title' => 'Commandes par menu', 'rows' => $orders]) ?>
            <?php partial('stats-chart', ['id' => 'chart-revenue', 'title' => 'Chiffre d\'affaires par menu', 'rows' => $revenue]) ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

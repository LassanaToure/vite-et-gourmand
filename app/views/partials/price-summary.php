<?php
$rules = order_config();
$known = $summary !== null;
$hasDiscount = $known && $summary['discount'] > 0;
$pending = $known && $summary['delivery'] === null;
$free = $known && !$pending && $summary['source'] === 'bordeaux';
$feeBase = money($rules['delivery_fee_cents']);
$perKm = money($rules['per_km_cents']);

if (!$known) {
    $menuLabel = 'Menu';
    $menuAmount = '—';
} else {
    $menuLabel = 'Menu : ' . money($summary['unit']) . ' × ' . $summary['people'] . ' personnes';
    $menuAmount = money($summary['base']);
}

if ($pending || !$known) {
    $deliveryDetail = 'Renseignez l\'adresse de livraison pour calculer les frais.';
    $deliveryAmount = '—';
} elseif ($free) {
    $deliveryDetail = 'Livraison offerte à Bordeaux.';
    $deliveryAmount = 'Offerte';
} else {
    $deliveryDetail = $feeBase . ' + ' . number_format($summary['km'], 1, ',', '') . ' km × ' . $perKm;
    $deliveryAmount = money($summary['delivery']);
}

$totalAmount = $known && $summary['total'] !== null ? money($summary['total']) : '—';
?>
<section class="price-summary" data-summary aria-labelledby="price-title">
    <h2 class="price-summary__title" id="price-title">Détail du prix</h2>
    <dl class="price-summary__list">
        <div class="price-summary__row">
            <dt data-field="menu-label"><?= e($menuLabel) ?></dt>
            <dd data-field="menu-amount"><?= e($menuAmount) ?></dd>
        </div>
        <div class="price-summary__row price-summary__row--discount" data-row="discount"<?= $hasDiscount ? '' : ' hidden' ?>>
            <dt data-field="discount-label">Réduction de <?= (int) $rules['discount_percent'] ?> %</dt>
            <dd data-field="discount-amount"><?= $hasDiscount ? e('−' . money($summary['discount'])) : '' ?></dd>
        </div>
        <div class="price-summary__row">
            <dt>Livraison <small class="price-summary__detail" data-field="delivery-detail"><?= e($deliveryDetail) ?></small></dt>
            <dd data-field="delivery-amount"><?= e($deliveryAmount) ?></dd>
        </div>
        <div class="price-summary__row price-summary__row--total">
            <dt>Total</dt>
            <dd data-field="total-amount" aria-live="polite"><?= e($totalAmount) ?></dd>
        </div>
    </dl>
</section>

<div class="admin-head">
    <p class="eyebrow"><a class="admin-back" href="<?= e(url('/admin/commandes/' . (int) $order['commande_id'])) ?>">Retour à la commande</a></p>
    <h1>Annuler la commande</h1>
</div>
<div class="card admin-block cancel-page">
    <?php partial('admin-cancel-form', ['order' => $order, 'errors' => $errors, 'old' => $old]) ?>
</div>

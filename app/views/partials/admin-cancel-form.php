<?php $action = url('/admin/commandes/' . (int) $order['commande_id'] . '/annuler'); ?>
<form class="cancel-form" method="post" action="<?= e($action) ?>" data-cancel-form>
    <?= csrf_field() ?>
    <h2 class="cancel-form__title" id="cancel-title">Annuler la commande <?= e($order['numero_commande']) ?></h2>
    <p class="form-note">Règle : une commande ne peut pas être annulée sans avoir d'abord contacté le client. Indiquez comment vous l'avez joint et pourquoi la commande est annulée.</p>
    <?php partial('form-errors', ['errors' => $errors]) ?>

    <fieldset class="choice<?= isset($errors['mode']) ? ' choice--error' : '' ?>" id="mode">
        <legend class="field__label">Client contacté par</legend>
        <label class="check"><input type="radio" name="mode" value="gsm" required<?= $old['mode'] === 'gsm' ? ' checked' : '' ?>> <span>Appel GSM (<?= e($order['telephone_contact']) ?>)</span></label>
        <label class="check"><input type="radio" name="mode" value="mail" required<?= $old['mode'] === 'mail' ? ' checked' : '' ?>> <span>E-mail (<?= e($order['email']) ?>)</span></label>
        <?php if (isset($errors['mode'])): ?>
            <p class="field__error"><?= e($errors['mode']) ?></p>
        <?php endif; ?>
    </fieldset>

    <?php partial('field', ['id' => 'motif', 'label' => 'Motif de l\'annulation', 'type' => 'textarea', 'rows' => 4, 'value' => $old['motif'], 'error' => $errors['motif'] ?? null, 'hint' => '10 caractères minimum. Ce motif est conservé sur la commande.', 'extra' => ['minlength' => '10', 'maxlength' => '500']]) ?>

    <div class="cancel-form__actions">
        <button class="btn" type="submit" data-confirm-cancel>Confirmer l'annulation</button>
        <button class="btn btn--outline" type="button" data-close-dialog>Fermer</button>
    </div>
</form>

<?php $action = url('/admin/commandes/' . (int) $order['commande_id'] . '/modifier'); ?>
<div class="admin-head">
    <p class="eyebrow"><a class="admin-back" href="<?= e(url('/admin/commandes/' . (int) $order['commande_id'])) ?>">Retour à la commande</a></p>
    <h1>Modifier la commande <?= e($order['numero_commande']) ?></h1>
</div>

<?php partial('form-errors', ['errors' => $errors]) ?>

<form class="admin-stack" method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>

    <section class="card admin-block" aria-labelledby="modify-presta">
        <h2 id="modify-presta">Prestation</h2>
        <?php partial('field', ['id' => 'date-prestation', 'name' => 'date_prestation', 'type' => 'date', 'label' => 'Date de la prestation', 'value' => $old['date_prestation'], 'error' => $errors['date_prestation'] ?? null]) ?>
        <?php partial('field', ['id' => 'heure-livraison', 'name' => 'heure_livraison', 'type' => 'time', 'label' => 'Heure de livraison', 'value' => $old['heure_livraison'], 'error' => $errors['heure_livraison'] ?? null]) ?>
        <?php partial('field', ['id' => 'nombre-personne', 'name' => 'nombre_personne', 'type' => 'number', 'label' => 'Nombre de personnes', 'value' => $old['nombre_personne'], 'error' => $errors['nombre_personne'] ?? null, 'extra' => ['min' => '1', 'max' => '9999']]) ?>
        <?php partial('field', ['id' => 'adresse', 'label' => 'Adresse de livraison', 'value' => $old['adresse'], 'error' => $errors['adresse'] ?? null]) ?>
        <?php partial('field', ['id' => 'code-postal', 'name' => 'code_postal', 'label' => 'Code postal', 'value' => $old['code_postal'], 'error' => $errors['code_postal'] ?? null, 'extra' => ['maxlength' => '5']]) ?>
        <?php partial('field', ['id' => 'ville', 'label' => 'Ville', 'value' => $old['ville'], 'error' => $errors['ville'] ?? null]) ?>
        <?php partial('field', ['id' => 'telephone', 'label' => 'Téléphone de contact', 'value' => $old['telephone'], 'error' => $errors['telephone'] ?? null]) ?>
    </section>

    <section class="card admin-block" aria-labelledby="modify-contact">
        <h2 id="modify-contact">Contact du client</h2>
        <p class="form-note">Règle : une commande ne peut pas être modifiée sans avoir d'abord contacté le client. Indiquez comment vous l'avez joint et pourquoi.</p>
        <fieldset class="choice<?= isset($errors['mode']) ? ' choice--error' : '' ?>" id="mode">
            <legend class="field__label">Client contacté par</legend>
            <label class="check"><input type="radio" name="mode" value="gsm" required<?= $contact['mode'] === 'gsm' ? ' checked' : '' ?>> <span>Appel GSM (<?= e($order['telephone_contact']) ?>)</span></label>
            <label class="check"><input type="radio" name="mode" value="mail" required<?= $contact['mode'] === 'mail' ? ' checked' : '' ?>> <span>E-mail (<?= e($order['email']) ?>)</span></label>
            <?php if (isset($errors['mode'])): ?>
                <p class="field__error"><?= e($errors['mode']) ?></p>
            <?php endif; ?>
        </fieldset>
        <?php partial('field', ['id' => 'motif', 'label' => 'Motif de la modification', 'type' => 'textarea', 'rows' => 4, 'value' => $contact['motif'], 'error' => $errors['motif'] ?? null, 'hint' => '10 caractères minimum.', 'extra' => ['minlength' => '10', 'maxlength' => '500']]) ?>
    </section>

    <div class="admin-actions">
        <button class="btn" type="submit">Enregistrer la modification</button>
        <a class="btn btn--outline" href="<?= e(url('/admin/commandes/' . (int) $order['commande_id'])) ?>">Annuler</a>
    </div>
</form>

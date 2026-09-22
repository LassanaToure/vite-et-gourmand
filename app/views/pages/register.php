<h1 class="auth-card__title">Créer un compte</h1>
<?php partial('form-errors', ['errors' => $errors]) ?>
<form method="post" action="<?= e(url('/inscription')) ?>">
    <?= csrf_field() ?>
    <p class="form-note">Tous les champs sont obligatoires.</p>
    <div class="field-row">
        <?php partial('field', ['id' => 'prenom', 'label' => 'Prénom', 'autocomplete' => 'given-name', 'value' => $old['prenom'] ?? '', 'error' => $errors['prenom'] ?? null]) ?>
        <?php partial('field', ['id' => 'nom', 'label' => 'Nom', 'autocomplete' => 'family-name', 'value' => $old['nom'] ?? '', 'error' => $errors['nom'] ?? null]) ?>
    </div>
    <?php partial('field', ['id' => 'email', 'label' => 'Email', 'type' => 'email', 'autocomplete' => 'email', 'placeholder' => 'vous@exemple.fr', 'value' => $old['email'] ?? '', 'error' => $errors['email'] ?? null]) ?>
    <?php partial('field', ['id' => 'telephone', 'label' => 'GSM', 'type' => 'tel', 'autocomplete' => 'tel', 'placeholder' => '06 12 34 56 78', 'value' => $old['telephone'] ?? '', 'error' => $errors['telephone'] ?? null]) ?>
    <?php partial('field', ['id' => 'adresse', 'label' => 'Adresse postale', 'autocomplete' => 'street-address', 'placeholder' => '14 rue du Palais Gallien', 'value' => $old['adresse'] ?? '', 'error' => $errors['adresse'] ?? null]) ?>
    <div class="field-row">
        <?php partial('field', ['id' => 'code-postal', 'name' => 'code_postal', 'label' => 'Code postal', 'autocomplete' => 'postal-code', 'placeholder' => '33000', 'value' => $old['code_postal'] ?? '', 'error' => $errors['code_postal'] ?? null, 'extra' => ['inputmode' => 'numeric', 'maxlength' => '5', 'pattern' => '[0-9]{5}']]) ?>
        <?php partial('field', ['id' => 'ville', 'label' => 'Ville', 'autocomplete' => 'address-level2', 'placeholder' => 'Bordeaux', 'value' => $old['ville'] ?? '', 'error' => $errors['ville'] ?? null]) ?>
    </div>
    <?php partial('field', ['id' => 'password', 'label' => 'Mot de passe', 'type' => 'password', 'autocomplete' => 'new-password', 'toggle' => true, 'hint' => '10 caractères minimum, avec une majuscule, une minuscule, un chiffre et un caractère spécial.', 'error' => $errors['password'] ?? null]) ?>
    <?php partial('field', ['id' => 'password-confirm', 'name' => 'password_confirm', 'label' => 'Confirmer le mot de passe', 'type' => 'password', 'autocomplete' => 'new-password', 'toggle' => true, 'error' => $errors['password_confirm'] ?? null]) ?>
    <button class="btn btn--block" type="submit">Créer mon compte</button>
</form>
<p class="auth-card__footer">Déjà un compte ? <a href="<?= e(url('/connexion')) ?>">Se connecter</a></p>

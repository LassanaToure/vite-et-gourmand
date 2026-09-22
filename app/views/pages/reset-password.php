<?php if ($invalid): ?>
    <h1 class="auth-card__title">Lien invalide</h1>
    <p class="auth-card__intro">Ce lien de réinitialisation est invalide, a expiré ou a déjà été utilisé. Faites une nouvelle demande pour recevoir un lien valable.</p>
    <a class="btn btn--block" href="<?= e(url('/mot-de-passe-oublie')) ?>">Refaire une demande</a>
<?php else: ?>
    <h1 class="auth-card__title">Nouveau mot de passe</h1>
    <?php partial('form-errors', ['errors' => $errors]) ?>
    <form method="post" action="<?= e(url('/reinitialiser-mot-de-passe')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <?php partial('field', ['id' => 'password', 'label' => 'Nouveau mot de passe', 'type' => 'password', 'autocomplete' => 'new-password', 'toggle' => true, 'hint' => '10 caractères minimum, avec une majuscule, une minuscule, un chiffre et un caractère spécial.', 'error' => $errors['password'] ?? null]) ?>
        <?php partial('field', ['id' => 'password-confirm', 'name' => 'password_confirm', 'label' => 'Confirmer le mot de passe', 'type' => 'password', 'autocomplete' => 'new-password', 'toggle' => true, 'error' => $errors['password_confirm'] ?? null]) ?>
        <button class="btn btn--block" type="submit">Enregistrer le mot de passe</button>
    </form>
<?php endif; ?>

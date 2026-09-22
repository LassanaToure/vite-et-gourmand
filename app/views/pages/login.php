<h1 class="auth-card__title">Connexion</h1>
<?php partial('form-errors', ['errors' => $errors]) ?>
<form method="post" action="<?= e(url('/connexion')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="retour" value="<?= e($return) ?>">
    <?php partial('field', ['id' => 'email', 'label' => 'Email', 'type' => 'email', 'autocomplete' => 'email', 'placeholder' => 'vous@exemple.fr', 'value' => $old['email'] ?? '']) ?>
    <?php partial('field', ['id' => 'password', 'label' => 'Mot de passe', 'type' => 'password', 'autocomplete' => 'current-password', 'toggle' => true]) ?>
    <p class="auth-card__aside"><a href="<?= e(url('/mot-de-passe-oublie')) ?>">Mot de passe oublié ?</a></p>
    <button class="btn btn--block" type="submit">Se connecter</button>
</form>
<p class="auth-card__footer">Pas encore de compte ? <a href="<?= e(url('/inscription')) ?>">Créer un compte</a></p>

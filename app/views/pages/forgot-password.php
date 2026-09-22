<h1 class="auth-card__title">Mot de passe oublié</h1>
<p class="auth-card__intro">Saisissez l'adresse email de votre compte. Nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>
<?php partial('form-errors', ['errors' => $errors]) ?>
<form method="post" action="<?= e(url('/mot-de-passe-oublie')) ?>">
    <?= csrf_field() ?>
    <?php partial('field', ['id' => 'email', 'label' => 'Email', 'type' => 'email', 'autocomplete' => 'email', 'placeholder' => 'vous@exemple.fr', 'value' => $old['email'] ?? '', 'error' => $errors['email'] ?? null]) ?>
    <button class="btn btn--block" type="submit">Envoyer le lien</button>
</form>
<p class="auth-card__footer"><a href="<?= e(url('/connexion')) ?>">Retour à la connexion</a></p>

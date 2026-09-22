<div class="admin-head">
    <p class="eyebrow"><a class="admin-back" href="<?= e(url('/admin/employes')) ?>">Comptes employés</a></p>
    <h1>Nouvel employé</h1>
</div>

<?php partial('form-errors', ['errors' => $errors]) ?>

<form class="card admin-block" method="post" action="<?= e(url('/admin/employes/nouveau')) ?>" novalidate>
    <?= csrf_field() ?>
    <p class="admin-note">Ce formulaire crée uniquement un compte <strong>employé</strong>. Un compte administrateur ne peut pas être créé depuis l'application.</p>
    <?php partial('field', ['id' => 'email', 'type' => 'email', 'label' => 'Adresse e-mail (identifiant)', 'value' => $old['email'] ?? '', 'error' => $errors['email'] ?? null, 'autocomplete' => 'off', 'extra' => ['maxlength' => '255']]) ?>
    <?php partial('field', ['id' => 'password', 'type' => 'password', 'label' => 'Mot de passe', 'toggle' => true, 'autocomplete' => 'new-password', 'hint' => 'Au moins 10 caractères, avec une majuscule, une minuscule, un chiffre et un caractère spécial.', 'error' => $errors['password'] ?? null]) ?>
    <?php partial('field', ['id' => 'password-confirm', 'name' => 'password_confirm', 'type' => 'password', 'label' => 'Confirmer le mot de passe', 'toggle' => true, 'autocomplete' => 'new-password', 'error' => $errors['password_confirm'] ?? null]) ?>
    <p class="admin-note">Un e-mail informe l'employé de la création de son compte, sans mot de passe : communiquez-le lui par un autre canal.</p>
    <div class="admin-actions">
        <button class="btn" type="submit">Créer le compte employé</button>
        <a class="btn btn--outline" href="<?= e(url('/admin/employes')) ?>">Annuler</a>
    </div>
</form>

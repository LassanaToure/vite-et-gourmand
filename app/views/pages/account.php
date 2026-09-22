<?php $roles = ['utilisateur' => 'Client', 'employe' => 'Employé', 'administrateur' => 'Administrateur']; ?>
<section class="section" aria-labelledby="account-title">
    <div class="container container--narrow">
        <p class="eyebrow">Espace personnel</p>
        <h1 id="account-title">Mon compte</h1>
        <p class="lead">Profil : <strong><?= e($roles[$user['role']] ?? $user['role']) ?></strong>. Gardez vos informations à jour pour faciliter vos commandes.</p>

        <section class="card account-block" aria-labelledby="profile-title">
            <h2 class="order-block__title" id="profile-title">Mes informations</h2>
            <?php partial('form-errors', ['errors' => $profileErrors]) ?>
            <form method="post" action="<?= e(url('/mon-compte/profil')) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <span class="field__label">Email</span>
                    <p class="account__email"><?= e($user['email']) ?></p>
                    <p class="field__hint">L'adresse e-mail est votre identifiant de connexion. Pour la changer, contactez-nous.</p>
                </div>
                <div class="field-row">
                    <?php partial('field', ['id' => 'prenom', 'label' => 'Prénom', 'autocomplete' => 'given-name', 'value' => $values['prenom'], 'error' => $profileErrors['prenom'] ?? null]) ?>
                    <?php partial('field', ['id' => 'nom', 'label' => 'Nom', 'autocomplete' => 'family-name', 'value' => $values['nom'], 'error' => $profileErrors['nom'] ?? null]) ?>
                </div>
                <?php partial('field', ['id' => 'telephone', 'label' => 'GSM', 'type' => 'tel', 'autocomplete' => 'tel', 'value' => $values['telephone'], 'error' => $profileErrors['telephone'] ?? null]) ?>
                <?php partial('field', ['id' => 'adresse', 'label' => 'Adresse postale', 'autocomplete' => 'street-address', 'value' => $values['adresse'], 'error' => $profileErrors['adresse'] ?? null]) ?>
                <div class="field-row">
                    <?php partial('field', ['id' => 'code-postal', 'name' => 'code_postal', 'label' => 'Code postal', 'autocomplete' => 'postal-code', 'value' => $values['code_postal'], 'error' => $profileErrors['code_postal'] ?? null, 'extra' => ['inputmode' => 'numeric', 'maxlength' => '5', 'pattern' => '[0-9]{5}']]) ?>
                    <?php partial('field', ['id' => 'ville', 'label' => 'Ville', 'autocomplete' => 'address-level2', 'value' => $values['ville'], 'error' => $profileErrors['ville'] ?? null]) ?>
                </div>
                <button class="btn" type="submit">Enregistrer mes informations</button>
            </form>
        </section>

        <section class="card account-block" aria-labelledby="security-title">
            <h2 class="order-block__title" id="security-title">Sécurité</h2>
            <?php partial('form-errors', ['errors' => $passwordErrors]) ?>
            <form method="post" action="<?= e(url('/mon-compte/mot-de-passe')) ?>">
                <?= csrf_field() ?>
                <?php partial('field', ['id' => 'current-password', 'name' => 'current_password', 'label' => 'Mot de passe actuel', 'type' => 'password', 'autocomplete' => 'current-password', 'toggle' => true, 'error' => $passwordErrors['current_password'] ?? null]) ?>
                <?php partial('field', ['id' => 'password', 'label' => 'Nouveau mot de passe', 'type' => 'password', 'autocomplete' => 'new-password', 'toggle' => true, 'hint' => '10 caractères minimum, avec une majuscule, une minuscule, un chiffre et un caractère spécial.', 'error' => $passwordErrors['password'] ?? null]) ?>
                <?php partial('field', ['id' => 'password-confirm', 'name' => 'password_confirm', 'label' => 'Confirmer le nouveau mot de passe', 'type' => 'password', 'autocomplete' => 'new-password', 'toggle' => true, 'error' => $passwordErrors['password_confirm'] ?? null]) ?>
                <button class="btn" type="submit">Changer mon mot de passe</button>
            </form>
        </section>
    </div>
</section>

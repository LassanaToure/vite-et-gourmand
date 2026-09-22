<?php
$site = site();
$openings = array_map(static fn (array $g): string => $g['name'] . ' ' . $g['label'], group_hours(opening_hours()));
$openings = array_filter($openings, static fn (string $line): bool => !str_ends_with($line, 'Fermé'));
?>
<section class="section contact" aria-labelledby="contact-title">
    <div class="container contact__grid">
        <div class="contact__info">
            <div class="page-intro">
                <p class="eyebrow">Restons en contact</p>
                <h1 id="contact-title">Nous contacter</h1>
                <p class="lead">Pour toute demande, devis ou question sur nos menus, notre équipe vous répond dans les 24 heures. Julie et José seront ravis d'échanger avec vous.</p>
            </div>
            <dl class="contact-list">
                <div class="contact-list__item">
                    <?php icon('pin', 'icon icon--lg') ?>
                    <div><dt>Adresse</dt><dd><?= e(implode(', ', $site['address'])) ?></dd></div>
                </div>
                <div class="contact-list__item">
                    <?php icon('phone', 'icon icon--lg') ?>
                    <div><dt>Téléphone</dt><dd><a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a></dd></div>
                </div>
                <div class="contact-list__item">
                    <?php icon('mail', 'icon icon--lg') ?>
                    <div><dt>Email</dt><dd><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></dd></div>
                </div>
                <div class="contact-list__item">
                    <?php icon('clock', 'icon icon--lg') ?>
                    <div><dt>Horaires</dt><dd><?= e(str_replace(' – ', '–', implode(' · ', $openings))) ?></dd></div>
                </div>
            </dl>
        </div>
        <div class="card contact-form">
            <h2>Envoyer un message</h2>
            <?php partial('form-errors', ['errors' => $errors]) ?>
            <form method="post" action="<?= e(url('/contact')) ?>" novalidate>
                <?= csrf_field() ?>
                <?php partial('field', ['id' => 'objet', 'label' => 'Objet / titre', 'placeholder' => 'Ex. : Demande de devis mariage', 'value' => $old['objet'] ?? input_string($_GET, 'objet'), 'error' => $errors['objet'] ?? null, 'extra' => ['maxlength' => '150']]) ?>
                <?php partial('field', ['id' => 'message', 'label' => 'Votre message', 'type' => 'textarea', 'rows' => 6, 'placeholder' => 'Décrivez votre événement, vos besoins...', 'value' => $old['message'] ?? null, 'error' => $errors['message'] ?? null, 'extra' => ['maxlength' => '2000']]) ?>
                <?php partial('field', ['id' => 'email', 'label' => 'Votre email', 'type' => 'email', 'autocomplete' => 'email', 'placeholder' => 'julie@exemple.fr', 'value' => $old['email'] ?? null, 'error' => $errors['email'] ?? null, 'extra' => ['maxlength' => '255']]) ?>
                <button class="btn btn--block" type="submit">Envoyer <span aria-hidden="true">→</span></button>
            </form>
        </div>
    </div>
</section>

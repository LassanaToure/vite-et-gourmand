<?php
$rows = [
    'Menu' => $order['menu_titre'],
    'Nombre de personnes' => (string) (int) $order['nombre_personne'],
    'Date de la prestation' => format_date($order['date_prestation']),
    'Heure de livraison' => substr($order['heure_livraison'], 0, 5),
    'Adresse' => $order['adresse_prestation'] . ', ' . $order['code_postal_prestation'] . ' ' . $order['ville_prestation'],
    'GSM de contact' => $order['telephone_contact'],
    'Statut' => 'En attente de validation par notre équipe',
];
?>
<section class="section order-confirmation" aria-labelledby="confirmation-title">
    <div class="container container--narrow">
        <p class="eyebrow">Commande enregistrée</p>
        <h1 id="confirmation-title">Merci <?= e($user['prenom'] ?? '') ?>, nous avons bien reçu votre commande</h1>
        <p class="lead">Votre numéro de commande est <strong><?= e($order['numero_commande']) ?></strong>. Un e-mail de confirmation vient de vous être envoyé.</p>

        <div class="card profile">
            <dl>
                <?php foreach ($rows as $label => $value): ?>
                    <div class="profile__row">
                        <dt><?= e($label) ?></dt>
                        <dd><?= e($value) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>

        <?php partial('price-summary', ['summary' => $summary]) ?>

        <section class="conditions-note" aria-labelledby="confirmation-conditions">
            <h2 id="confirmation-conditions">Conditions de ce menu</h2>
            <p><?= e($order['menu_conditions']) ?></p>
        </section>

        <div class="order-confirmation__actions">
            <a class="btn" href="<?= e(url('/menus')) ?>">Retour aux menus</a>
            <a class="btn btn--outline" href="<?= e(url('/mon-compte')) ?>">Mon compte</a>
        </div>
    </div>
</section>

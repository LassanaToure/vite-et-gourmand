<?php
$statuses = order_statuses();
$path = url('/admin/commandes/' . (int) $order['commande_id']);
$client = trim(($order['prenom'] ?? '') . ' ' . ($order['nom'] ?? ''));
$modes = ['gsm' => 'appel GSM', 'mail' => 'e-mail'];
$phone = preg_replace('/[^\d+]/', '', (string) $order['telephone_contact']);
$prestation = [
    'Date de la prestation' => format_date($order['date_prestation']),
    'Heure de livraison' => substr($order['heure_livraison'], 0, 5),
    'Nombre de personnes' => (string) (int) $order['nombre_personne'],
    'Adresse' => $order['adresse_prestation'] . ', ' . $order['code_postal_prestation'] . ' ' . $order['ville_prestation'],
    'Matériel prêté' => (bool) $order['pret_materiel'] ? ((bool) $order['restitution_materiel'] ? 'Oui, restitué' : 'Oui, à restituer') : 'Non',
];
?>
<div class="admin-head admin-head--split">
    <div>
        <p class="eyebrow"><a class="admin-back" href="<?= e(url('/admin/commandes')) ?>">Commandes</a></p>
        <h1><?= e($order['numero_commande']) ?></h1>
        <p><?= e($order['menu_titre']) ?> pour <?= (int) $order['nombre_personne'] ?> personnes</p>
    </div>
    <?php partial('status-badge', ['code' => $order['statut']]) ?>
</div>

<div class="admin-grid">
    <div class="admin-stack">
        <section class="card admin-block" aria-labelledby="block-client">
            <h2 id="block-client">Client</h2>
            <dl class="identity">
                <div><dt>Nom</dt><dd><?= e($client) ?></dd></div>
                <div><dt>E-mail</dt><dd><a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a></dd></div>
                <div><dt>GSM de la commande</dt><dd><a href="tel:<?= e($phone) ?>"><?= e($order['telephone_contact']) ?></a></dd></div>
                <?php if (!empty($order['compte_telephone']) && $order['compte_telephone'] !== $order['telephone_contact']): ?>
                    <div><dt>GSM du compte</dt><dd><?= e($order['compte_telephone']) ?></dd></div>
                <?php endif; ?>
            </dl>
        </section>

        <section class="card admin-block" aria-labelledby="block-presta">
            <h2 id="block-presta">Prestation</h2>
            <dl class="identity">
                <?php foreach ($prestation as $label => $value): ?>
                    <div><dt><?= e($label) ?></dt><dd><?= e($value) ?></dd></div>
                <?php endforeach; ?>
            </dl>
        </section>

        <?php if ($order['statut'] === 'annulee'): ?>
            <section class="card admin-block" aria-labelledby="block-cancel">
                <h2 id="block-cancel">Annulation</h2>
                <dl class="identity">
                    <div><dt>Client contacté par</dt><dd><?= e($modes[$order['mode_contact_annulation']] ?? 'Annulation à la demande du client') ?></dd></div>
                    <div><dt>Motif</dt><dd><?= e($order['motif_annulation'] ?? '') ?></dd></div>
                </dl>
            </section>
        <?php endif; ?>

        <section class="card admin-block" aria-labelledby="block-history">
            <h2 id="block-history">Historique</h2>
            <table class="admin-table admin-table--compact">
                <thead><tr><th scope="col">Date</th><th scope="col">Statut</th><th scope="col">Détail</th><th scope="col">Par</th></tr></thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= e(format_datetime($row['date_modification'])) ?></td>
                            <td><?= e($statuses[$row['statut']]['label']) ?></td>
                            <td><?= e($row['commentaire'] ?? '') ?></td>
                            <td><?= e($row['auteur'] ?? 'Client') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <aside class="admin-stack">
        <?php partial('price-summary', ['summary' => $summary]) ?>

        <section class="card admin-block" aria-labelledby="block-actions">
            <h2 id="block-actions">Actions</h2>
            <?php if ($next !== null): ?>
                <form class="admin-stack" method="post" action="<?= e($path . '/statut') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="from" value="<?= e($order['statut']) ?>">
                    <?php if ($extra === 'lend'): ?>
                        <label class="check"><input type="checkbox" name="lend" value="1"> <span>Du matériel a été prêté au client (plats, couverts, contenants isothermes)</span></label>
                    <?php elseif ($extra === 'return'): ?>
                        <label class="check"><input type="checkbox" name="returned" value="1" required> <span>Le matériel a été restitué par le client</span></label>
                    <?php endif; ?>
                    <button class="btn btn--block" type="submit"><?= e(OrderFlow::actionLabel($next)) ?> <span aria-hidden="true">→</span></button>
                    <p class="form-note">Prochain statut : <strong><?= e($statuses[$next]['label']) ?></strong></p>
                </form>
            <?php else: ?>
                <p class="form-note">Cette commande est <?= $order['statut'] === 'annulee' ? 'annulée' : 'terminée' ?> : son statut ne peut plus changer.</p>
            <?php endif; ?>

            <?php if ($cancellable): ?>
                <a class="btn btn--block btn--outline" href="<?= e($path . '/modifier') ?>">Modifier la commande</a>
                <a class="btn btn--block btn--outline" href="<?= e($path . '/annuler') ?>" data-open-cancel>Annuler la commande</a>
                <p class="form-note">Contactez d'abord le client (GSM : <a href="tel:<?= e($phone) ?>"><?= e($order['telephone_contact']) ?></a>).</p>
            <?php endif; ?>
        </section>
    </aside>
</div>

<?php if ($cancellable): ?>
    <dialog class="dialog" id="cancel-dialog" aria-labelledby="cancel-title">
        <?php partial('admin-cancel-form', ['order' => $order, 'errors' => [], 'old' => ['mode' => '', 'motif' => '']]) ?>
    </dialog>
<?php endif; ?>

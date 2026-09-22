<?php
$labels = ['en_attente' => 'En attente', 'valide' => 'Validés', 'refuse' => 'Refusés'];
$badges = ['en_attente' => 'En attente de validation', 'valide' => 'Publié', 'refuse' => 'Refusé'];
$tones = ['en_attente' => 'pending', 'valide' => 'done', 'refuse' => 'cancelled'];
?>
<div class="admin-head">
    <h1>Avis clients</h1>
    <p>Validez les avis pour les publier sur la page d'accueil, ou refusez-les.</p>
</div>

<div class="tabs" role="tablist" aria-label="Filtrer les avis" data-tabs hidden>
    <button class="tabs__tab" type="button" role="tab" id="tab-en_attente" aria-selected="true" aria-controls="reviews-panel" data-filter="en_attente">En attente (<?= (int) $counts['en_attente'] ?>)</button>
    <button class="tabs__tab" type="button" role="tab" id="tab-valide" aria-selected="false" aria-controls="reviews-panel" tabindex="-1" data-filter="valide">Validés (<?= (int) $counts['valide'] ?>)</button>
    <button class="tabs__tab" type="button" role="tab" id="tab-refuse" aria-selected="false" aria-controls="reviews-panel" tabindex="-1" data-filter="refuse">Refusés (<?= (int) $counts['refuse'] ?>)</button>
</div>

<div id="reviews-panel" role="tabpanel" aria-labelledby="tab-en_attente" tabindex="0" data-panel data-default="en_attente">
    <ul class="review-list list-reset">
        <?php foreach ($reviews as $review): ?>
            <?php
            $author = trim(($review['prenom'] ?? '') . ' ' . ($review['nom'] ?? ''));
            $status = $review['statut'];
            $base = url('/admin/avis/' . (int) $review['avis_id']);
            ?>
            <li data-tab-item data-group="<?= e($status) ?>">
                <article class="card review-admin">
                    <header class="review-admin__head">
                        <div>
                            <p class="review-admin__author"><?= e($author) ?> <small><?= e($review['email']) ?></small></p>
                            <p class="review-admin__meta">Commande <?= e($review['numero_commande']) ?>, <?= e($review['menu_titre']) ?>, le <?= e(format_date(substr($review['date_creation'], 0, 10))) ?></p>
                        </div>
                        <span class="status status--<?= e($tones[$status]) ?>"><?= e($badges[$status]) ?></span>
                    </header>
                    <p class="review-admin__stars" aria-label="Note : <?= (int) $review['note'] ?> sur 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php icon('star', 'icon icon--sm' . ($i <= (int) $review['note'] ? ' icon--fill' : '')) ?>
                        <?php endfor; ?>
                        <span><?= (int) $review['note'] ?> sur 5</span>
                    </p>
                    <p class="review-admin__text"><?= e($review['description']) ?></p>
                    <div class="review-admin__actions">
                        <?php if ($status !== 'valide'): ?>
                            <form method="post" action="<?= e($base . '/valider') ?>"><?= csrf_field() ?><button class="btn btn--small" type="submit">Valider et publier</button></form>
                        <?php endif; ?>
                        <?php if ($status !== 'refuse'): ?>
                            <form method="post" action="<?= e($base . '/refuser') ?>"><?= csrf_field() ?><button class="btn btn--small btn--outline" type="submit"><?= $status === 'valide' ? 'Retirer de l\'accueil' : 'Refuser' ?></button></form>
                        <?php endif; ?>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="admin-empty" data-tab-empty hidden>Aucun avis dans cet onglet.</p>
</div>

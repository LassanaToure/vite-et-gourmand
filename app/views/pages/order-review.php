<?php $path = url('/mes-commandes/' . (int) $order['commande_id']); ?>
<section class="section" aria-labelledby="review-title">
    <div class="container container--narrow">
        <p class="eyebrow">Commande <?= e($order['numero_commande']) ?></p>
        <h1 id="review-title">Donner mon avis</h1>
        <p class="lead">Comment s'est passée votre prestation « <?= e($order['menu_titre']) ?> » ? Votre avis sera publié après validation par notre équipe.</p>

        <?php partial('form-errors', ['errors' => $errors]) ?>

        <form class="card" method="post" action="<?= e($path . '/avis') ?>">
            <?= csrf_field() ?>
            <fieldset class="rating<?= isset($errors['note']) ? ' rating--error' : '' ?>" id="note">
                <legend class="field__label">Votre note</legend>
                <div class="rating__stars">
                    <?php for ($value = 5; $value >= 1; $value--): ?>
                        <input type="radio" id="note-<?= $value ?>" name="note" value="<?= $value ?>" required<?= (string) $value === $old['note'] ? ' checked' : '' ?>>
                        <label for="note-<?= $value ?>">
                            <?php icon('star', 'icon icon--lg') ?>
                            <span class="sr-only"><?= $value ?> sur 5</span>
                        </label>
                    <?php endfor; ?>
                </div>
                <?php if (isset($errors['note'])): ?>
                    <p class="field__error"><?= e($errors['note']) ?></p>
                <?php endif; ?>
            </fieldset>

            <?php partial('field', ['id' => 'commentaire', 'label' => 'Votre commentaire', 'type' => 'textarea', 'rows' => 6, 'value' => $old['commentaire'], 'error' => $errors['commentaire'] ?? null, 'hint' => 'Entre 10 et 1000 caractères.', 'extra' => ['minlength' => '10', 'maxlength' => '1000']]) ?>

            <div class="order-confirmation__actions">
                <button class="btn" type="submit">Envoyer mon avis</button>
                <a class="btn btn--outline" href="<?= e($path) ?>">Retour à la commande</a>
            </div>
        </form>
    </div>
</section>

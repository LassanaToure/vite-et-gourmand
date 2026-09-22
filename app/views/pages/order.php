<?php
$mode = $mode ?? 'create';
$editing = $mode === 'edit';
$action = $action ?? '/commande';
$unit = $unit ?? null;
$current = null;
foreach ($menus as $menu) {
    if ((int) $menu['menu_id'] === $selected) {
        $current = $menu;
    }
}
$soldOut = $current !== null && (int) $current['quantite_restante'] < 1;
$menuError = $errors['menu_id'] ?? ($soldOut ? 'Ce menu n\'est plus disponible pour le moment.' : null);
$minPeople = $current === null ? 1 : (int) $current['nombre_personne_minimum'];
$delay = $current === null ? 0 : (int) $current['delai_minimum_jours'];
$earliest = OrderValidator::earliestDate($delay);
$latest = OrderValidator::earliestDate($rules['max_days_ahead']);
$config = [
    'deliveryFeeCents' => $rules['delivery_fee_cents'],
    'perKmCents' => $rules['per_km_cents'],
    'discountPercent' => $rules['discount_percent'],
    'discountExtraPeople' => $rules['discount_extra_people'],
    'maxDays' => $rules['max_days_ahead'],
];
?>
<section class="section order" aria-labelledby="order-title">
    <div class="container">
        <div class="page-intro">
            <p class="eyebrow"><?= $editing ? 'Commande ' . e($order['numero_commande']) : 'Votre commande' ?></p>
            <h1 id="order-title"><?= $editing ? 'Modifier ma commande' : 'Commander un menu' ?></h1>
            <p class="lead"><?= $editing ? 'Vous pouvez tout modifier sauf le menu, tant que votre commande n\'a pas été acceptée. Le prix est recalculé à chaque modification.' : 'Choisissez votre menu, indiquez où et quand nous devons vous livrer : le prix est détaillé avant validation.' ?></p>
        </div>

        <?php partial('form-errors', ['errors' => $errors]) ?>

        <form class="order-form" method="post" action="<?= e(url($action)) ?>" data-order data-endpoint="<?= e(url('/commande/livraison')) ?>" data-rules="<?= e(json_encode($config)) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="order_nonce" value="<?= e($nonce) ?>">

            <div class="order-form__grid">
                <div class="order-form__main">
                    <fieldset class="card order-block">
                        <legend class="order-block__title">1. Votre menu</legend>
                        <div class="field">
                            <label class="field__label" for="menu-id">Menu</label>
                            <select class="input<?= $menuError !== null ? ' input--error' : '' ?>" id="menu-id" name="menu_id"<?= $editing ? ' disabled' : ' required' ?> data-menu<?= $menuError !== null ? ' aria-invalid="true" aria-describedby="menu-id-error"' : '' ?>>
                                <option value=""<?= $current === null ? ' selected' : '' ?>>Choisir un menu</option>
                                <?php foreach ($menus as $menu): ?>
                                    <?php $isCurrent = (int) $menu['menu_id'] === $selected; $full = (int) $menu['quantite_restante'] < 1; ?>
                                    <option value="<?= (int) $menu['menu_id'] ?>"
                                        data-price="<?= $isCurrent && $unit !== null ? $unit : OrderPricing::cents($menu['prix_par_personne']) ?>"
                                        data-min="<?= (int) $menu['nombre_personne_minimum'] ?>"
                                        data-delay="<?= (int) $menu['delai_minimum_jours'] ?>"
                                        data-conditions="<?= e($menu['conditions']) ?>"
                                        <?= $isCurrent ? 'selected' : '' ?><?= $full && !$isCurrent ? ' disabled' : '' ?>>
                                        <?= e($menu['titre']) ?> (<?= e(price((float) $menu['prix_par_personne'])) ?> / personne<?= $full ? ', complet' : '' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($editing): ?>
                                <p class="field__hint">Le choix du menu ne peut pas être modifié. Pour commander un autre menu, annulez cette commande et passez-en une nouvelle.</p>
                            <?php endif; ?>
                            <?php if ($menuError !== null): ?>
                                <p class="field__error" id="menu-id-error"><?= e($menuError) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="field">
                            <label class="field__label" for="nombre-personne">Nombre de personnes</label>
                            <div class="stepper" data-stepper>
                                <button class="stepper__button" type="button" data-step="-1" aria-label="Retirer une personne">−</button>
                                <input class="input stepper__input<?= isset($errors['nombre_personne']) ? ' input--error' : '' ?>" id="nombre-personne" name="nombre_personne" type="number" inputmode="numeric" min="<?= $minPeople ?>" max="9999" step="1" value="<?= e((string) ($old['nombre_personne'] !== '' ? $old['nombre_personne'] : ($current === null ? '' : $minPeople))) ?>" required aria-describedby="nombre-personne-hint<?= isset($errors['nombre_personne']) ? ' nombre-personne-error' : '' ?>"<?= isset($errors['nombre_personne']) ? ' aria-invalid="true"' : '' ?>>
                                <button class="stepper__button" type="button" data-step="1" aria-label="Ajouter une personne">+</button>
                            </div>
                            <p class="field__hint" id="nombre-personne-hint">
                                Minimum <span data-field="minimum"><?= $minPeople ?></span> personnes pour ce menu.
                                Dès <span data-field="discount-from"><?= $minPeople + (int) $rules['discount_extra_people'] ?></span> personnes, <?= (int) $rules['discount_percent'] ?> % de réduction.
                            </p>
                            <?php if (isset($errors['nombre_personne'])): ?>
                                <p class="field__error" id="nombre-personne-error"><?= e($errors['nombre_personne']) ?></p>
                            <?php endif; ?>
                        </div>
                    </fieldset>

                    <fieldset class="card order-block">
                        <legend class="order-block__title">2. Vos coordonnées</legend>
                        <dl class="identity">
                            <div><dt>Nom</dt><dd><?= e(trim(($user['nom'] ?? '') . '')) ?: 'Non renseigné' ?></dd></div>
                            <div><dt>Prénom</dt><dd><?= e($user['prenom'] ?? '') ?: 'Non renseigné' ?></dd></div>
                            <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
                        </dl>
                        <p class="form-note">Ces informations proviennent de votre compte.</p>
                        <?php partial('field', ['id' => 'telephone', 'label' => 'GSM de contact', 'type' => 'tel', 'autocomplete' => 'tel', 'value' => $old['telephone'], 'error' => $errors['telephone'] ?? null, 'placeholder' => '06 12 34 56 78']) ?>
                    </fieldset>

                    <fieldset class="card order-block">
                        <legend class="order-block__title">3. Livraison et prestation</legend>
                        <?php partial('field', ['id' => 'adresse', 'label' => 'Adresse de livraison', 'autocomplete' => 'street-address', 'value' => $old['adresse'], 'error' => $errors['adresse'] ?? null, 'placeholder' => '14 rue du Palais Gallien']) ?>
                        <div class="field-row">
                            <?php partial('field', ['id' => 'code-postal', 'name' => 'code_postal', 'label' => 'Code postal', 'autocomplete' => 'postal-code', 'value' => $old['code_postal'], 'error' => $errors['code_postal'] ?? null, 'placeholder' => '33000', 'extra' => ['inputmode' => 'numeric', 'maxlength' => '5', 'pattern' => '[0-9]{5}']]) ?>
                            <?php partial('field', ['id' => 'ville', 'label' => 'Ville', 'autocomplete' => 'address-level2', 'value' => $old['ville'], 'error' => $errors['ville'] ?? null, 'placeholder' => 'Bordeaux']) ?>
                        </div>
                        <div class="field-row">
                            <?php partial('field', ['id' => 'date-prestation', 'name' => 'date_prestation', 'label' => 'Date de la prestation', 'type' => 'date', 'value' => $old['date_prestation'], 'error' => $errors['date_prestation'] ?? null, 'extra' => ['min' => $earliest, 'max' => $latest], 'hint' => $current === null ? null : 'Ce menu se commande ' . $delay . ' jours à l\'avance.']) ?>
                            <?php partial('field', ['id' => 'heure-livraison', 'name' => 'heure_livraison', 'label' => 'Heure de livraison souhaitée', 'type' => 'time', 'value' => $old['heure_livraison'], 'error' => $errors['heure_livraison'] ?? null, 'extra' => ['min' => $rules['delivery_hours'][0], 'max' => $rules['delivery_hours'][1], 'step' => '900']]) ?>
                        </div>
                        <p class="order-block__status" role="status" data-delivery-status></p>
                    </fieldset>
                </div>

                <aside class="order-form__side">
                    <section class="conditions-note" aria-labelledby="order-conditions-title" data-conditions<?= $current === null ? ' hidden' : '' ?>>
                        <h2 id="order-conditions-title">Conditions de ce menu</h2>
                        <p data-field="conditions"><?= e($current['conditions'] ?? '') ?></p>
                    </section>

                    <?php partial('price-summary', ['summary' => $summary]) ?>

                    <div class="field">
                        <label class="check" for="accept">
                            <input type="checkbox" id="accept" name="accept" value="1" required<?= $old['accept'] === '1' ? ' checked' : '' ?><?= isset($errors['accept']) ? ' aria-invalid="true" aria-describedby="accept-error"' : '' ?>>
                            <span>J'ai pris connaissance des conditions de ce menu et des <a href="<?= e(url('/cgv')) ?>" target="_blank" rel="noopener">CGV<span class="sr-only"> (nouvel onglet)</span></a>.</span>
                        </label>
                        <?php if (isset($errors['accept'])): ?>
                            <p class="field__error" id="accept-error"><?= e($errors['accept']) ?></p>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn--block" type="submit" data-submit><?= $editing ? 'Enregistrer les modifications' : 'Confirmer la commande' ?> <span aria-hidden="true">→</span></button>
                    <?php if ($editing): ?>
                        <a class="btn btn--block btn--outline" href="<?= e(url('/mes-commandes/' . (int) $order['commande_id'])) ?>">Annuler les modifications</a>
                    <?php endif; ?>
                    <p class="order-form__note">Le prix définitif est calculé par nos serveurs au moment de la validation.</p>
                </aside>
            </div>
        </form>
    </div>
</section>

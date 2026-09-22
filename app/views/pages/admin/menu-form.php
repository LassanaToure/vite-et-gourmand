<?php
$editing = $menu !== null;
$base = $editing ? url('/admin/menus/' . (int) $menu['menu_id']) : null;
$action = $editing ? $base . '/modifier' : url('/admin/menus/nouveau');
$categories = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
$selectedPlats = array_map('intval', $values['plats']);
$commandes = $editing ? (int) $menu['commandes'] : 0;
$active = !$editing || (bool) $menu['actif'];
$slotCount = max(0, min(3, (int) $slots));
?>
<div class="admin-head admin-head--split">
    <div>
        <p class="eyebrow"><a class="admin-back" href="<?= e(url('/admin/menus')) ?>">Menus</a></p>
        <h1><?= $editing ? e($menu['titre']) : 'Nouveau menu' ?></h1>
    </div>
    <?php if ($editing): ?>
        <span class="status status--<?= $active ? 'done' : 'cancelled' ?>"><?= $active ? 'Visible sur le site' : 'Archivé' ?></span>
    <?php endif; ?>
</div>

<?php partial('form-errors', ['errors' => $errors]) ?>

<form class="admin-stack" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <section class="card admin-block" aria-labelledby="menu-general">
        <h2 id="menu-general">Informations</h2>
        <?php partial('field', ['id' => 'titre', 'label' => 'Titre', 'value' => $values['titre'], 'error' => $errors['titre'] ?? null, 'extra' => ['maxlength' => '100']]) ?>
        <?php partial('field', ['id' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 5, 'value' => $values['description'], 'error' => $errors['description'] ?? null, 'hint' => 'Présentation du menu (20 caractères minimum).']) ?>
        <?php partial('field', ['id' => 'conditions', 'label' => 'Conditions du menu', 'type' => 'textarea', 'rows' => 3, 'value' => $values['conditions'], 'error' => $errors['conditions'] ?? null, 'hint' => 'Affichées en évidence avant la commande (stockage, précautions, délai...).']) ?>
        <div class="field-row">
            <div class="field">
                <label class="field__label" for="theme-id">Thème</label>
                <select class="input<?= isset($errors['theme_id']) ? ' input--error' : '' ?>" id="theme-id" name="theme_id" required<?= isset($errors['theme_id']) ? ' aria-invalid="true"' : '' ?>>
                    <option value="">Choisir</option>
                    <?php foreach ($references['themes'] as $theme): ?>
                        <option value="<?= (int) $theme['id'] ?>"<?= (string) $values['theme_id'] === (string) $theme['id'] ? ' selected' : '' ?>><?= e($theme['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['theme_id'])): ?><p class="field__error"><?= e($errors['theme_id']) ?></p><?php endif; ?>
            </div>
            <div class="field">
                <label class="field__label" for="regime-id">Régime</label>
                <select class="input<?= isset($errors['regime_id']) ? ' input--error' : '' ?>" id="regime-id" name="regime_id" required<?= isset($errors['regime_id']) ? ' aria-invalid="true"' : '' ?>>
                    <option value="">Choisir</option>
                    <?php foreach ($references['regimes'] as $regime): ?>
                        <option value="<?= (int) $regime['id'] ?>"<?= (string) $values['regime_id'] === (string) $regime['id'] ? ' selected' : '' ?>><?= e($regime['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['regime_id'])): ?><p class="field__error"><?= e($errors['regime_id']) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="admin-fields">
            <?php partial('field', ['id' => 'nombre-personne-minimum', 'name' => 'nombre_personne_minimum', 'label' => 'Personnes minimum', 'type' => 'number', 'value' => $values['nombre_personne_minimum'], 'error' => $errors['nombre_personne_minimum'] ?? null, 'extra' => ['min' => '1', 'max' => '1000', 'step' => '1']]) ?>
            <?php partial('field', ['id' => 'prix-par-personne', 'name' => 'prix_par_personne', 'label' => 'Prix par personne (€)', 'value' => $values['prix_par_personne'], 'error' => $errors['prix_par_personne'] ?? null, 'extra' => ['inputmode' => 'decimal']]) ?>
            <?php partial('field', ['id' => 'quantite-restante', 'name' => 'quantite_restante', 'label' => 'Stock (commandes possibles)', 'type' => 'number', 'value' => $values['quantite_restante'], 'error' => $errors['quantite_restante'] ?? null, 'extra' => ['min' => '0', 'max' => '9999', 'step' => '1']]) ?>
            <?php partial('field', ['id' => 'delai-minimum-jours', 'name' => 'delai_minimum_jours', 'label' => 'Délai de commande (jours)', 'type' => 'number', 'value' => $values['delai_minimum_jours'], 'error' => $errors['delai_minimum_jours'] ?? null, 'extra' => ['min' => '0', 'max' => '365', 'step' => '1']]) ?>
        </div>
    </section>

    <section class="card admin-block" aria-labelledby="menu-plats">
        <h2 id="menu-plats">Plats du menu</h2>
        <?php foreach ($categories as $key => $label): ?>
            <fieldset class="choice">
                <legend class="field__label"><?= e($label) ?></legend>
                <div class="choice__grid">
                    <?php foreach ($references['plats'] as $plat): ?>
                        <?php if ($plat['categorie'] !== $key) { continue; } ?>
                        <label class="check"><input type="checkbox" name="plats[]" value="<?= (int) $plat['plat_id'] ?>"<?= in_array((int) $plat['plat_id'], $selectedPlats, true) ? ' checked' : '' ?>> <span><?= e($plat['titre_plat']) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endforeach; ?>
        <p class="form-note">Un plat peut figurer dans plusieurs menus. <a href="<?= e(url('/admin/plats/nouveau')) ?>">Créer un plat</a></p>
    </section>

    <section class="card admin-block" aria-labelledby="menu-images">
        <h2 id="menu-images">Galerie d'images</h2>
        <p class="form-note">JPEG, PNG ou WebP, 3 Mo maximum. La première image (ordre le plus bas) est l'image principale. Le texte alternatif décrit l'image pour les lecteurs d'écran.</p>
        <?php if (isset($errors['images'])): ?><p class="field__error"><?= e($errors['images']) ?></p><?php endif; ?>

        <?php foreach ($images as $image): ?>
            <?php $id = (int) $image['image_id']; ?>
            <div class="image-row">
                <img class="image-row__thumb" src="<?= e(asset($image['chemin'])) ?>" alt="" width="120" height="90">
                <div class="image-row__fields">
                    <div class="field">
                        <label class="field__label" for="image-alt-<?= $id ?>">Texte alternatif</label>
                        <input class="input<?= isset($errors['image-' . $id]) ? ' input--error' : '' ?>" id="image-alt-<?= $id ?>" name="images[<?= $id ?>][alt]" value="<?= e($image['texte_alternatif']) ?>" maxlength="255">
                        <?php if (isset($errors['image-' . $id])): ?><p class="field__error"><?= e($errors['image-' . $id]) ?></p><?php endif; ?>
                    </div>
                    <div class="image-row__side">
                        <div class="field">
                            <label class="field__label" for="image-order-<?= $id ?>">Ordre</label>
                            <input class="input" type="number" id="image-order-<?= $id ?>" name="images[<?= $id ?>][ordre]" value="<?= (int) $image['ordre'] ?>" min="0" max="99">
                        </div>
                        <label class="check"><input type="checkbox" name="images[<?= $id ?>][delete]" value="1"> <span>Supprimer</span></label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php for ($i = 0; $i < $slotCount; $i++): ?>
            <div class="image-row image-row--new">
                <div class="image-row__fields">
                    <div class="field">
                        <label class="field__label" for="new-image-<?= $i ?>">Ajouter une image</label>
                        <input class="input" type="file" id="new-image-<?= $i ?>" name="new_images[<?= $i ?>]" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="field">
                        <label class="field__label" for="new-alt-<?= $i ?>">Texte alternatif</label>
                        <input class="input<?= isset($errors['new-' . $i]) ? ' input--error' : '' ?>" id="new-alt-<?= $i ?>" name="new_alt[<?= $i ?>]" maxlength="255">
                        <?php if (isset($errors['new-' . $i])): ?><p class="field__error"><?= e($errors['new-' . $i]) ?></p><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </section>

    <div class="admin-actions">
        <button class="btn" type="submit"><?= $editing ? 'Enregistrer le menu' : 'Créer le menu' ?></button>
        <a class="btn btn--outline" href="<?= e(url('/admin/menus')) ?>">Annuler</a>
    </div>
</form>

<?php if ($editing): ?>
    <section class="card admin-block danger-zone" aria-labelledby="menu-danger">
        <h2 id="menu-danger">Archivage et suppression</h2>
        <p><?= $commandes > 0 ? $commandes . ' commande' . ($commandes > 1 ? 's utilisent' : ' utilise') . ' ce menu : il ne peut pas être supprimé, mais vous pouvez l\'archiver. Son historique de commandes est conservé.' : 'Aucune commande n\'utilise ce menu : vous pouvez le supprimer définitivement.' ?></p>
        <div class="admin-actions">
            <?php if ($active): ?>
                <form method="post" action="<?= e($base . '/archiver') ?>"><?= csrf_field() ?><button class="btn btn--outline" type="submit">Archiver (masquer du site)</button></form>
            <?php else: ?>
                <form method="post" action="<?= e($base . '/restaurer') ?>"><?= csrf_field() ?><button class="btn btn--outline" type="submit">Restaurer (afficher sur le site)</button></form>
            <?php endif; ?>
            <?php if ($commandes === 0): ?>
                <details class="danger-zone__confirm">
                    <summary class="btn btn--outline">Supprimer définitivement</summary>
                    <form method="post" action="<?= e($base . '/supprimer') ?>">
                        <?= csrf_field() ?>
                        <p>Cette action supprime le menu, ses liaisons avec les plats et ses images téléversées. Elle est irréversible.</p>
                        <button class="btn" type="submit">Oui, supprimer ce menu</button>
                    </form>
                </details>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php
$editing = $plat !== null;
$action = $editing ? url('/admin/plats/' . (int) $plat['plat_id'] . '/modifier') : url('/admin/plats/nouveau');
$categories = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];
$selectedAllergens = array_map('intval', $values['allergenes']);
$selectedMenus = array_map('intval', $values['menus']);
?>
<div class="admin-head">
    <p class="eyebrow"><a class="admin-back" href="<?= e(url('/admin/plats')) ?>">Plats</a></p>
    <h1><?= $editing ? e($plat['titre_plat']) : 'Nouveau plat' ?></h1>
</div>

<?php partial('form-errors', ['errors' => $errors]) ?>

<form class="admin-stack" method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>

    <section class="card admin-block" aria-labelledby="plat-general">
        <h2 id="plat-general">Informations</h2>
        <?php partial('field', ['id' => 'titre-plat', 'name' => 'titre_plat', 'label' => 'Libellé', 'value' => $values['titre_plat'], 'error' => $errors['titre_plat'] ?? null, 'extra' => ['maxlength' => '100']]) ?>
        <div class="field">
            <label class="field__label" for="categorie">Type</label>
            <select class="input<?= isset($errors['categorie']) ? ' input--error' : '' ?>" id="categorie" name="categorie" required<?= isset($errors['categorie']) ? ' aria-invalid="true"' : '' ?>>
                <option value="">Choisir</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= e($key) ?>"<?= $values['categorie'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['categorie'])): ?><p class="field__error"><?= e($errors['categorie']) ?></p><?php endif; ?>
        </div>
    </section>

    <section class="card admin-block" aria-labelledby="plat-allergenes">
        <h2 id="plat-allergenes">Allergènes</h2>
        <fieldset class="choice">
            <legend class="sr-only">Allergènes présents dans ce plat</legend>
            <div class="choice__grid choice__grid--tags">
                <?php foreach ($references['allergenes'] as $allergene): ?>
                    <label class="tag"><input type="checkbox" name="allergenes[]" value="<?= (int) $allergene['id'] ?>"<?= in_array((int) $allergene['id'], $selectedAllergens, true) ? ' checked' : '' ?>> <span><?= e($allergene['libelle']) ?></span></label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </section>

    <section class="card admin-block" aria-labelledby="plat-menus">
        <h2 id="plat-menus">Menus concernés</h2>
        <fieldset class="choice">
            <legend class="sr-only">Menus contenant ce plat</legend>
            <div class="choice__grid">
                <?php foreach ($references['menus'] as $menu): ?>
                    <label class="check"><input type="checkbox" name="menus[]" value="<?= (int) $menu['menu_id'] ?>"<?= in_array((int) $menu['menu_id'], $selectedMenus, true) ? ' checked' : '' ?>> <span><?= e($menu['titre']) ?><?= (bool) $menu['actif'] ? '' : ' (archivé)' ?></span></label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </section>

    <div class="admin-actions">
        <button class="btn" type="submit"><?= $editing ? 'Enregistrer le plat' : 'Créer le plat' ?></button>
        <a class="btn btn--outline" href="<?= e(url('/admin/plats')) ?>">Annuler</a>
    </div>
</form>

<?php if ($editing): ?>
    <section class="card admin-block danger-zone" aria-labelledby="plat-danger">
        <h2 id="plat-danger">Suppression</h2>
        <?php if ($selectedMenus === []): ?>
            <p>Ce plat n'est rattaché à aucun menu : vous pouvez le supprimer.</p>
            <details class="danger-zone__confirm">
                <summary class="btn btn--outline">Supprimer ce plat</summary>
                <form method="post" action="<?= e(url('/admin/plats/' . (int) $plat['plat_id'] . '/supprimer')) ?>">
                    <?= csrf_field() ?>
                    <p>Cette action est irréversible.</p>
                    <button class="btn" type="submit">Oui, supprimer ce plat</button>
                </form>
            </details>
        <?php else: ?>
            <p>Ce plat est rattaché à au moins un menu. Décochez d'abord tous les menus ci-dessus et enregistrez pour pouvoir le supprimer.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

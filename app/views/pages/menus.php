<?php
$total = count($menus);
$value = static fn (string $key): string => e((string) ($filters[$key] ?? ''));
$selected = static fn (string $key, int $id): string => ($filters[$key] ?? null) === $id ? ' selected' : '';
?>
<section class="section menus" aria-labelledby="menus-title">
    <div class="container">
        <div class="page-intro">
            <p class="eyebrow">Notre carte</p>
            <h1 id="menus-title">Nos menus</h1>
            <p class="lead">Mariages, fêtes de fin d'année, événements d'entreprise : trouvez le menu qui sublimera votre table.</p>
        </div>

        <form class="card filters" action="<?= e(url('/menus')) ?>" method="get" data-filters data-endpoint="<?= e(url('/api/menus.php')) ?>">
            <h2 class="sr-only">Filtrer les menus</h2>
            <div class="filters__grid">
                <div class="field">
                    <label class="field__label" for="prix-max">Prix maximum (€)</label>
                    <input class="input" type="number" id="prix-max" name="prix_max" min="0" step="1" inputmode="numeric" placeholder="Ex. : 60" value="<?= $value('prix_max') ?>">
                </div>
                <fieldset class="field filters__range">
                    <legend class="field__label">Fourchette de prix (€)</legend>
                    <div class="filters__range-inputs">
                        <label class="sr-only" for="fourchette-min">Prix minimum</label>
                        <input class="input" type="number" id="fourchette-min" name="fourchette_min" min="0" step="1" inputmode="numeric" placeholder="De" value="<?= $value('fourchette_min') ?>">
                        <label class="sr-only" for="fourchette-max">Prix maximum de la fourchette</label>
                        <input class="input" type="number" id="fourchette-max" name="fourchette_max" min="0" step="1" inputmode="numeric" placeholder="À" value="<?= $value('fourchette_max') ?>">
                    </div>
                </fieldset>
                <div class="field">
                    <label class="field__label" for="theme">Thème</label>
                    <select class="input" id="theme" name="theme">
                        <option value="">Tous les thèmes</option>
                        <?php foreach ($themes as $theme): ?>
                            <option value="<?= (int) $theme['id'] ?>"<?= $selected('theme', (int) $theme['id']) ?>><?= e($theme['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label class="field__label" for="regime">Régime</label>
                    <select class="input" id="regime" name="regime">
                        <option value="">Tous les régimes</option>
                        <?php foreach ($regimes as $regime): ?>
                            <option value="<?= (int) $regime['id'] ?>"<?= $selected('regime', (int) $regime['id']) ?>><?= e($regime['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label class="field__label" for="personnes">Nombre de personnes</label>
                    <input class="input" type="number" id="personnes" name="personnes" min="1" step="1" inputmode="numeric" placeholder="Ex. : 12" value="<?= $value('personnes') ?>">
                </div>
            </div>
            <div class="filters__actions">
                <button class="btn" type="submit">Filtrer</button>
                <button class="btn btn--outline" type="reset">Réinitialiser</button>
            </div>
        </form>

        <p class="filters__count" role="status" data-count><?= $total === 0 ? 'Aucun menu' : $total . ($total > 1 ? ' menus' : ' menu') ?></p>
        <p class="menus__error" role="alert" data-error hidden>Impossible de charger les menus. Réessayez dans un instant.</p>

        <ul class="menu-grid list-reset" data-menu-list>
            <?php foreach ($menus as $menu): ?>
                <li><?php partial('menu-card', ['menu' => $menu]) ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="menus__empty" data-empty<?= $total === 0 ? '' : ' hidden' ?>>Aucun menu ne correspond à votre recherche. Modifiez ou réinitialisez les filtres.</p>

        <template data-menu-template>
            <li><?php partial('menu-card', ['menu' => null]) ?></li>
        </template>
    </div>
</section>

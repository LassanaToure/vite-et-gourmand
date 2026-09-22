<div class="admin-head">
    <h1>Horaires</h1>
    <p>Ces horaires sont affichés dans le pied de page du site et sur la page contact.</p>
</div>

<?php partial('form-errors', ['errors' => $errors]) ?>

<form class="card admin-block" method="post" action="<?= e(url('/admin/horaires')) ?>">
    <?= csrf_field() ?>
    <table class="admin-table admin-table--compact hours-table">
        <caption class="sr-only">Horaires d'ouverture du lundi au dimanche</caption>
        <thead>
            <tr>
                <th scope="col">Jour</th>
                <th scope="col">Ouverture</th>
                <th scope="col">Fermeture</th>
                <th scope="col">Fermé</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($days as $day): ?>
                <?php $id = (int) $day['horaire_id']; $row = $values[$id]; $invalid = isset($errors['hours-' . $id]); ?>
                <tr>
                    <th scope="row"><?= e($day['jour']) ?></th>
                    <td>
                        <label class="sr-only" for="hours-<?= $id ?>">Ouverture <?= e($day['jour']) ?></label>
                        <input class="input<?= $invalid ? ' input--error' : '' ?>" type="time" id="hours-<?= $id ?>" name="hours[<?= $id ?>][open]" value="<?= e($row['open']) ?>" data-hours-time>
                    </td>
                    <td>
                        <label class="sr-only" for="close-<?= $id ?>">Fermeture <?= e($day['jour']) ?></label>
                        <input class="input<?= $invalid ? ' input--error' : '' ?>" type="time" id="close-<?= $id ?>" name="hours[<?= $id ?>][close]" value="<?= e($row['close']) ?>" data-hours-time>
                    </td>
                    <td>
                        <label class="check"><input type="checkbox" name="hours[<?= $id ?>][closed]" value="1"<?= $row['closed'] ? ' checked' : '' ?>> <span class="sr-only">Fermé le <?= e($day['jour']) ?></span><span aria-hidden="true">Fermé</span></label>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="admin-actions">
        <button class="btn" type="submit">Enregistrer les horaires</button>
    </div>
</form>

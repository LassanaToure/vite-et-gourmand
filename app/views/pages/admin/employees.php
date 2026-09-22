<div class="admin-head admin-head--split">
    <div>
        <h1>Comptes employés</h1>
        <p>L'adresse e-mail sert d'identifiant. Un compte désactivé ne peut plus se connecter et sa session ouverte est fermée.</p>
    </div>
    <a class="btn" href="<?= e(url('/admin/employes/nouveau')) ?>">Nouvel employé</a>
</div>

<p class="admin-note">Les comptes administrateur ne peuvent pas être créés depuis l'application.</p>

<?php if ($employees === []): ?>
    <p class="admin-empty">Aucun compte employé pour le moment.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="admin-table">
            <caption class="sr-only">Liste des comptes employés</caption>
            <thead>
                <tr>
                    <th scope="col">Employé</th>
                    <th scope="col">Créé le</th>
                    <th scope="col">Statut</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <?php
                    $name = trim(($employee['prenom'] ?? '') . ' ' . ($employee['nom'] ?? ''));
                    $active = (bool) $employee['actif'];
                    $action = url('/admin/employes/' . (int) $employee['id'] . ($active ? '/desactiver' : '/reactiver'));
                    ?>
                    <tr<?= $active ? '' : ' class="admin-table__muted"' ?>>
                        <th scope="row"><?= e($employee['email']) ?><?php if ($name !== ''): ?><span class="admin-table__sub"><?= e($name) ?></span><?php endif; ?></th>
                        <td><?= e(format_date(substr($employee['date_creation'], 0, 10))) ?></td>
                        <td><span class="status status--<?= $active ? 'done' : 'cancelled' ?>"><?= $active ? 'Actif' : 'Désactivé' ?></span></td>
                        <td class="admin-table__actions">
                            <form method="post" action="<?= e($action) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn--small btn--outline" type="submit"><?= $active ? 'Désactiver' : 'Réactiver' ?><span class="sr-only"> <?= e($employee['email']) ?></span></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

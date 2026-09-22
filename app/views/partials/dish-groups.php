<?php $categories = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts']; ?>
<div class="grid grid--3">
    <?php foreach ($categories as $key => $label): ?>
        <?php if ($plats[$key] === []) { continue; } ?>
        <article class="card dish-group">
            <h3><?= e($label) ?></h3>
            <ul class="list-reset">
                <?php foreach ($plats[$key] as $dish): ?>
                    <li class="dish">
                        <p class="dish__name"><?= e($dish['titre']) ?></p>
                        <p class="dish__allergens">
                            <?= $dish['allergenes'] === [] ? 'Aucun allergène majeur' : 'Allergènes : ' . e(implode(', ', $dish['allergenes'])) ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endforeach; ?>
</div>

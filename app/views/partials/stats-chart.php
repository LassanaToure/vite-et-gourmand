<?php
$max = max(array_column($rows, 'value'));
?>
<figure class="card stats-chart" aria-labelledby="<?= e($id) ?>-title">
    <figcaption class="stats-chart__title" id="<?= e($id) ?>-title"><?= e($title) ?></figcaption>
    <ul class="stats-chart__list list-reset">
        <?php foreach ($rows as $row): ?>
            <?php $percent = $max > 0 ? round($row['value'] / $max * 100, 1) : 0; ?>
            <li class="stats-chart__row">
                <span class="stats-chart__label"><?= e($row['label']) ?></span>
                <span class="stats-chart__track" aria-hidden="true">
                    <span class="stats-chart__bar<?= $row['value'] === $max && $max > 0 ? ' stats-chart__bar--top' : '' ?>" style="width: <?= e((string) $percent) ?>%"></span>
                </span>
                <span class="stats-chart__value"><?= e($row['text']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</figure>

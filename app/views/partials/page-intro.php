<div class="page-intro">
    <p class="eyebrow"><?= e($eyebrow) ?></p>
    <h1><?= e($heading) ?></h1>
    <?php if (!empty($text)): ?>
        <p class="lead"><?= e($text) ?></p>
    <?php endif; ?>
</div>

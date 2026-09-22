<?php $messages = Session::pullFlash(); ?>
<?php if ($messages !== []): ?>
    <div class="flash-list<?= !empty($wrap) ? ' container' : '' ?>">
        <?php foreach ($messages as $message): ?>
            <p class="flash flash--<?= e($message['type']) ?>" role="status"><?= e($message['text']) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($errors !== []): ?>
    <div class="form-errors" role="alert">
        <p class="form-errors__title"><?= count($errors) > 1 ? 'Le formulaire contient des erreurs' : 'Le formulaire contient une erreur' ?></p>
        <ul>
            <?php foreach ($errors as $key => $message): ?>
                <li>
                    <?php if ($key === 'form'): ?>
                        <?= e($message) ?>
                    <?php else: ?>
                        <a href="#<?= e(str_replace('_', '-', (string) $key)) ?>"><?= e($message) ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

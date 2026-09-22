<?php
$type = $type ?? 'text';
$name = $name ?? $id;
$required = $required ?? true;
$hint = $hint ?? null;
$toggle = $toggle ?? false;
$autocomplete = $autocomplete ?? null;
$placeholder = $placeholder ?? null;
$rows = $rows ?? 5;
$value = $type === 'password' ? null : ($value ?? null);
$error = $error ?? null;
$extra = $extra ?? [];
$describedBy = trim(($hint !== null ? $id . '-hint ' : '') . ($error !== null ? $id . '-error' : ''));
$attributes = 'id="' . e($id) . '" name="' . e($name) . '"'
    . ($placeholder !== null ? ' placeholder="' . e($placeholder) . '"' : '')
    . ($autocomplete !== null ? ' autocomplete="' . e($autocomplete) . '"' : '')
    . ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '')
    . ($error !== null ? ' aria-invalid="true"' : '')
    . implode('', array_map(static fn (string $key, string $val): string => ' ' . e($key) . '="' . e($val) . '"', array_keys($extra), $extra))
    . ($required ? ' required' : '');
$inputClass = 'input' . ($toggle ? ' input--toggle' : '') . ($error !== null ? ' input--error' : '');
?>
<div class="field">
    <label class="field__label" for="<?= e($id) ?>"><?= e($label) ?><?php if (!$required): ?> <span class="field__optional">(facultatif)</span><?php endif; ?></label>
    <div class="field__control">
        <?php if ($type === 'textarea'): ?>
            <textarea class="<?= $inputClass ?>" rows="<?= (int) $rows ?>" <?= $attributes ?>><?= e($value) ?></textarea>
        <?php else: ?>
            <input class="<?= $inputClass ?>" type="<?= e($type) ?>" <?= $attributes ?><?= $value !== null ? ' value="' . e($value) . '"' : '' ?>>
        <?php endif; ?>
        <?php if ($toggle): ?>
            <button class="field__toggle" type="button" data-password-toggle aria-controls="<?= e($id) ?>" aria-pressed="false" aria-label="Afficher le mot de passe">
                <?php icon('eye', 'icon') ?>
            </button>
        <?php endif; ?>
    </div>
    <?php if ($hint !== null): ?>
        <p class="field__hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p class="field__error" id="<?= e($id) ?>-error"><?= e($error) ?></p>
    <?php endif; ?>
</div>

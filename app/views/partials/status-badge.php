<?php $status = order_statuses()[$code] ?? ['label' => $code, 'tone' => 'pending']; ?>
<span class="status status--<?= e($status['tone']) ?>"><?= e($status['label']) ?></span>

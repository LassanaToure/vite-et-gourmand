<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $count = (new OrderStatsSync())->syncAll();
    fwrite(STDOUT, $count . " commande(s) synchronisée(s) vers MongoDB.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'Synchronisation impossible : ' . $exception->getMessage() . "\n");
    exit(1);
}

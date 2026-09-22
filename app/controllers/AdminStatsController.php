<?php
declare(strict_types=1);

final class AdminStatsController
{
    public static function index(array $params, array $query): array
    {
        [$errors, $filters] = StatsQuery::filters($query);
        $report = null;

        try {
            $report = StatsQuery::report($filters);
        } catch (Throwable $exception) {
            error_log('[stats] lecture MongoDB impossible : ' . $exception->getMessage());
        }

        return ['vars' => ['errors' => $errors, 'filters' => $filters, 'report' => $report]];
    }

    public static function synchronize(array $params, array $query): array
    {
        if (is_post()) {
            try {
                $count = (new OrderStatsSync())->syncAll();
                Session::flash('success', $count . ' commande(s) synchronisée(s) depuis MySQL vers MongoDB.');
            } catch (Throwable $exception) {
                error_log('[stats] resynchronisation impossible : ' . $exception->getMessage());
                Session::flash('error', 'La synchronisation a échoué : MongoDB est injoignable.');
            }
        }

        return ['redirect' => '/admin/stats'];
    }
}

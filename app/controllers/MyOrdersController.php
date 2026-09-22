<?php
declare(strict_types=1);

final class MyOrdersController
{
    public static function index(array $params, array $query): array
    {
        $orders = (new OrderModel())->listForUser((int) Auth::user()['id']);
        $counts = ['all' => count($orders), 'en_cours' => 0, 'terminee' => 0];
        foreach ($orders as $order) {
            $counts[order_statuses()[$order['statut']]['group']]++;
        }

        return ['vars' => ['orders' => $orders, 'counts' => $counts]];
    }

    public static function show(array $params, array $query): ?array
    {
        $order = self::owned($params);
        if ($order === null) {
            return null;
        }

        return [
            'title' => 'Commande ' . $order['numero_commande'],
            'vars' => [
                'order' => $order,
                'summary' => OrderPricing::fromOrder($order),
                'plats' => (new PlatModel())->forMenu((int) $order['menu_id']),
                'review' => (new ReviewModel())->findForOrder((int) $order['commande_id']),
                'user' => Auth::user(),
            ],
        ];
    }

    public static function cancel(array $params, array $query): ?array
    {
        $order = self::owned($params);
        if ($order === null) {
            return null;
        }

        $detail = self::detailPath($order);
        if ($order['statut'] !== 'en_attente') {
            Session::flash('error', 'Cette commande a été acceptée : elle ne peut plus être annulée.');
            return ['redirect' => $detail];
        }

        if (!is_post()) {
            return ['vars' => ['order' => $order, 'summary' => OrderPricing::fromOrder($order)]];
        }

        $result = (new OrderModel())->cancel((int) $order['commande_id'], (int) Auth::user()['id']);
        if (($result['error'] ?? null) === 'notfound') {
            return null;
        }
        if (isset($result['error'])) {
            Session::flash('error', 'Cette commande a été acceptée : elle ne peut plus être annulée.');
            return ['redirect' => $detail];
        }

        $user = Auth::user();
        Mailer::send($user['email'], 'Annulation de votre commande ' . $order['numero_commande'], implode("\n", [
            'Bonjour ' . ($user['prenom'] ?? '') . ',',
            '',
            'Votre commande ' . $order['numero_commande'] . ' (' . $order['menu_titre'] . ') a bien été annulée à votre demande.',
            '',
            'Julie et José',
        ]));
        Session::flash('success', 'Votre commande ' . $order['numero_commande'] . ' a été annulée.');

        return ['redirect' => $detail];
    }

    public static function tracking(array $params, array $query): ?array
    {
        $order = self::owned($params);
        if ($order === null) {
            return null;
        }

        if ($order['statut'] === 'en_attente') {
            Session::flash('error', 'Le suivi sera disponible dès que votre commande aura été acceptée.');
            return ['redirect' => self::detailPath($order)];
        }

        $rows = (new OrderModel())->timeline((int) $order['commande_id']);

        return [
            'title' => 'Suivi de la commande ' . $order['numero_commande'],
            'vars' => [
                'order' => $order,
                'timeline' => OrderTimeline::build($order, $rows),
                'deadline' => $order['statut'] === 'attente_retour_materiel' ? OrderTimeline::returnDeadline($rows) : null,
            ],
        ];
    }

    public static function review(array $params, array $query): ?array
    {
        $order = self::owned($params);
        if ($order === null) {
            return null;
        }

        $detail = self::detailPath($order);
        $reviews = new ReviewModel();
        if ($order['statut'] !== 'terminee') {
            Session::flash('error', 'Vous pourrez donner votre avis une fois la commande terminée.');
            return ['redirect' => $detail];
        }
        if ($reviews->findForOrder((int) $order['commande_id']) !== null) {
            Session::flash('info', 'Vous avez déjà donné votre avis sur cette commande.');
            return ['redirect' => $detail];
        }

        $errors = [];
        $old = ['note' => '', 'commentaire' => ''];

        if (is_post()) {
            $old = ['note' => input_string($_POST, 'note'), 'commentaire' => input_string($_POST, 'commentaire')];
            [$errors, $clean] = ReviewValidator::validate($old);

            if ($errors === []) {
                try {
                    $reviews->create((int) Auth::user()['id'], (int) $order['commande_id'], $clean['note'], $clean['commentaire']);
                    Session::flash('success', 'Merci pour votre avis ! Il sera publié après validation par notre équipe.');
                } catch (PDOException $exception) {
                    if (($exception->errorInfo[1] ?? 0) !== 1062) {
                        throw $exception;
                    }
                    Session::flash('info', 'Vous avez déjà donné votre avis sur cette commande.');
                }

                return ['redirect' => $detail];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['order' => $order, 'errors' => $errors, 'old' => $old],
        ];
    }

    private static function owned(array $params): ?array
    {
        return (new OrderModel())->findForUser((int) $params['id'], (int) Auth::user()['id']);
    }

    private static function detailPath(array $order): string
    {
        return '/mes-commandes/' . (int) $order['commande_id'];
    }
}

<?php
declare(strict_types=1);

final class AdminOrderController
{
    private const MESSAGES = [
        'stale' => 'Le statut de la commande a changé entre-temps. Vérifiez son état actuel avant de continuer.',
        'final' => 'Cette commande est terminée ou annulée : son statut ne peut plus changer.',
        'confirm' => 'Confirmez que le matériel a bien été restitué pour terminer la commande.',
        'status' => 'Cette commande ne peut plus être annulée ou modifiée à ce stade.',
        'minimum' => 'Le nombre de personnes est inférieur au minimum de ce menu.',
    ];

    private const QUOTE_MESSAGES = [
        'unavailable' => 'Impossible de calculer les frais de livraison pour le moment. Réessayez dans quelques instants.',
        'too_far' => 'Cette adresse est hors de notre zone de livraison.',
        'invalid' => 'Renseignez l\'adresse, le code postal et la ville.',
    ];

    private const FIELDS = ['telephone', 'adresse', 'code_postal', 'ville', 'date_prestation', 'heure_livraison', 'nombre_personne'];

    public static function root(array $params, array $query): array
    {
        return ['redirect' => '/admin/commandes'];
    }

    public static function index(array $params, array $query): array
    {
        $orders = (new OrderAdminModel())->listAll();
        $counts = array_fill_keys(array_keys(order_statuses()), 0);
        foreach ($orders as $order) {
            $counts[$order['statut']]++;
        }

        return ['vars' => ['orders' => $orders, 'counts' => $counts]];
    }

    public static function show(array $params, array $query): ?array
    {
        $order = (new OrderAdminModel())->findAny((int) $params['id']);
        if ($order === null) {
            return null;
        }

        return [
            'title' => 'Commande ' . $order['numero_commande'],
            'vars' => self::detailVars($order),
        ];
    }

    public static function advance(array $params, array $query): ?array
    {
        $id = (int) $params['id'];
        $detail = '/admin/commandes/' . $id;
        if (!is_post()) {
            return ['redirect' => $detail];
        }

        $model = new OrderAdminModel();
        $result = $model->advance($id, input_string($_POST, 'from'), [
            'lend' => input_string($_POST, 'lend') === '1',
            'returned' => input_string($_POST, 'returned') === '1',
        ], (int) Auth::user()['id']);

        if (($result['error'] ?? null) === 'notfound') {
            return null;
        }
        if (isset($result['error'])) {
            Session::flash('error', self::MESSAGES[$result['error']]);
            return ['redirect' => $detail];
        }

        $order = $model->findAny($id);
        self::notify($result['next'], $order);
        Session::flash('success', 'Statut mis à jour : ' . order_statuses()[$result['next']]['label'] . '.');

        return ['redirect' => $detail];
    }

    public static function cancel(array $params, array $query): ?array
    {
        $id = (int) $params['id'];
        $model = new OrderAdminModel();
        $order = $model->findAny($id);
        if ($order === null) {
            return null;
        }

        $detail = '/admin/commandes/' . $id;
        if (!OrderFlow::canCancel($order)) {
            Session::flash('error', self::MESSAGES['status']);
            return ['redirect' => $detail];
        }

        $errors = [];
        $old = ['mode' => '', 'motif' => ''];

        if (is_post()) {
            $old = ['mode' => input_string($_POST, 'mode'), 'motif' => input_string($_POST, 'motif')];
            $errors = self::validateContact($old, 'l\'annulation');

            if ($errors === []) {
                $result = $model->cancel($id, $old['mode'], $old['motif'], (int) Auth::user()['id']);
                if (isset($result['error'])) {
                    Session::flash('error', self::MESSAGES['status']);
                    return ['redirect' => $detail];
                }

                self::notifyCancellation($order, $old);
                Session::flash('success', 'La commande ' . $order['numero_commande'] . ' a été annulée.');

                return ['redirect' => $detail];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['order' => $order, 'errors' => $errors, 'old' => $old],
        ];
    }

    public static function modify(array $params, array $query): ?array
    {
        $id = (int) $params['id'];
        $model = new OrderAdminModel();
        $order = $model->findAny($id);
        if ($order === null) {
            return null;
        }

        $detail = '/admin/commandes/' . $id;
        if (!OrderFlow::canCancel($order)) {
            Session::flash('error', self::MESSAGES['status']);
            return ['redirect' => $detail];
        }

        $menu = (new MenuModel())->find((int) $order['menu_id']);
        $errors = [];
        $old = self::fromOrder($order);
        $contact = ['mode' => '', 'motif' => ''];

        if (is_post()) {
            $old = self::posted();
            $contact = ['mode' => input_string($_POST, 'mode'), 'motif' => input_string($_POST, 'motif')];
            $errors = self::validateContact($contact, 'la modification');

            [$formErrors, $clean] = OrderValidator::validate(
                $old,
                $menu,
                ['check_stock' => false, 'keep_date' => $order['date_prestation'], 'require_accept' => false]
            );
            $errors += $formErrors;

            if ($errors === []) {
                $quote = (new DeliveryQuote())->forAddress($clean['adresse'], $clean['code_postal'], $clean['ville']);
                if (!$quote['ok']) {
                    $errors['adresse'] = self::QUOTE_MESSAGES[$quote['error']];
                }
            }

            if ($errors === []) {
                $result = $model->update($id, $clean, $quote, $contact['mode'], $contact['motif'], (int) Auth::user()['id']);
                if (isset($result['error'])) {
                    if ($result['error'] === 'notfound') {
                        return null;
                    }
                    Session::flash('error', self::MESSAGES[$result['error']] ?? self::MESSAGES['status']);
                    return ['redirect' => $detail];
                }

                self::notifyModification($order, $result, $clean, $contact);
                Session::flash('success', 'La commande ' . $order['numero_commande'] . ' a été modifiée.');

                return ['redirect' => $detail];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['order' => $order, 'errors' => $errors, 'old' => $old, 'contact' => $contact],
        ];
    }

    private static function detailVars(array $order): array
    {
        $model = new OrderAdminModel();

        return [
            'order' => $order,
            'summary' => OrderPricing::fromOrder($order),
            'history' => $model->history((int) $order['commande_id']),
            'next' => OrderFlow::next($order),
            'extra' => OrderFlow::extra($order),
            'cancellable' => OrderFlow::canCancel($order),
        ];
    }

    private static function notify(string $status, array $order): void
    {
        $greeting = 'Bonjour ' . ($order['prenom'] ?? '') . ',';

        if ($status === 'attente_retour_materiel') {
            $deadline = OrderTimeline::addBusinessDays(date('Y-m-d'), 10);
            Mailer::send($order['email'], 'Retour du matériel : commande ' . $order['numero_commande'], implode("\n", [
                $greeting,
                '',
                'Votre commande ' . $order['numero_commande'] . ' a été livrée. Le matériel prêté (plats, couverts, contenants isothermes) doit nous être restitué sous 10 jours ouvrés, soit avant le ' . format_date($deadline) . '.',
                'Au-delà, une pénalité forfaitaire de 600,00 € sera facturée (article 5 des conditions générales de vente).',
                'Pour organiser le retour, contactez-nous : ' . app_url('/contact'),
                '',
                'Julie et José',
            ]));
        }

        if ($status === 'terminee') {
            Mailer::send($order['email'], 'Votre commande ' . $order['numero_commande'] . ' est terminée', implode("\n", [
                $greeting,
                '',
                'Votre commande ' . $order['numero_commande'] . ' est terminée. Merci de votre confiance !',
                'Vous pouvez maintenant donner votre avis depuis votre compte : ' . app_url('/mes-commandes/' . (int) $order['commande_id'] . '/avis'),
                '',
                'Julie et José',
            ]));
        }
    }

    private static function notifyModification(array $order, array $result, array $clean, array $contact): void
    {
        $pricing = $result['pricing'];
        Mailer::send($order['email'], 'Modification de votre commande ' . $order['numero_commande'], implode("\n", [
            'Bonjour ' . ($order['prenom'] ?? '') . ',',
            '',
            'Après ' . ($contact['mode'] === 'gsm' ? 'notre appel' : 'notre e-mail') . ', votre commande ' . $order['numero_commande'] . ' (' . $result['menu']['titre'] . ') a été modifiée par notre équipe.',
            $clean['nombre_personne'] . ' personnes, le ' . format_date($clean['date_prestation']) . ' à ' . $clean['heure_livraison'] . ', ' . $clean['adresse'] . ', ' . $clean['code_postal'] . ' ' . $clean['ville'] . '.',
            'Menu : ' . money($pricing['menu']) . ' | Livraison : ' . money($pricing['delivery']) . ' | Total : ' . money($pricing['total']),
            'Motif : ' . $contact['motif'],
            '',
            'Nous restons à votre disposition : ' . app_url('/contact'),
            '',
            'Julie et José',
        ]));
    }

    private static function validateContact(array $contact, string $noun): array
    {
        $errors = [];
        if (!in_array($contact['mode'], ['gsm', 'mail'], true)) {
            $errors['mode'] = 'Indiquez comment vous avez contacté le client (appel GSM ou e-mail).';
        }
        if (mb_strlen($contact['motif']) < 10 || mb_strlen($contact['motif']) > 500) {
            $errors['motif'] = 'Le motif de ' . $noun . ' est obligatoire (10 à 500 caractères).';
        }

        return $errors;
    }

    private static function posted(): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            $values[$field] = input_string($_POST, $field);
        }

        return $values;
    }

    private static function fromOrder(array $order): array
    {
        return [
            'telephone' => (string) $order['telephone_contact'],
            'adresse' => (string) $order['adresse_prestation'],
            'code_postal' => (string) $order['code_postal_prestation'],
            'ville' => (string) $order['ville_prestation'],
            'date_prestation' => (string) $order['date_prestation'],
            'heure_livraison' => substr((string) $order['heure_livraison'], 0, 5),
            'nombre_personne' => (string) $order['nombre_personne'],
        ];
    }

    private static function notifyCancellation(array $order, array $cancellation): void
    {
        Mailer::send($order['email'], 'Annulation de votre commande ' . $order['numero_commande'], implode("\n", [
            'Bonjour ' . ($order['prenom'] ?? '') . ',',
            '',
            'Après ' . ($cancellation['mode'] === 'gsm' ? 'notre appel' : 'notre e-mail') . ', nous avons dû annuler votre commande ' . $order['numero_commande'] . ' (' . $order['menu_titre'] . ').',
            'Motif : ' . $cancellation['motif'],
            '',
            'Nous restons à votre disposition : ' . app_url('/contact'),
            '',
            'Julie et José',
        ]));
    }
}

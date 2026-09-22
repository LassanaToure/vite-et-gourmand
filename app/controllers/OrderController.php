<?php
declare(strict_types=1);

final class OrderController
{
    private const FIELDS = ['telephone', 'adresse', 'code_postal', 'ville', 'date_prestation', 'heure_livraison', 'nombre_personne', 'accept'];

    private const REJECTIONS = [
        'menu' => ['menu_id', 'Choisissez un menu.'],
        'stock' => ['menu_id', 'Ce menu n\'est plus disponible pour le moment.'],
        'minimum' => ['nombre_personne', 'Le nombre de personnes est inférieur au minimum de ce menu.'],
        'delai' => ['date_prestation', 'La date de prestation ne respecte pas le délai de commande de ce menu.'],
    ];

    private const QUOTE_MESSAGES = [
        'unavailable' => 'Nous ne pouvons pas calculer les frais de livraison pour le moment. Réessayez dans quelques instants ou appelez-nous pour finaliser votre commande.',
        'too_far' => 'Cette adresse est hors de notre zone de livraison. Contactez-nous pour étudier votre demande.',
        'invalid' => 'Renseignez l\'adresse, le code postal et la ville pour calculer la livraison.',
    ];

    public static function form(array $params, array $query): array
    {
        $user = Auth::user();
        $menuModel = new MenuModel();
        $menus = $menuModel->search([]);
        $errors = [];
        $old = self::defaults($user);
        $selected = self::identifier(is_post() ? ($_POST['menu_id'] ?? '') : ($query['menuId'] ?? ''));
        $quote = null;

        if (is_post()) {
            $old = self::posted();

            if (!self::nonceValid()) {
                return self::recentlyOrdered() ? ['redirect' => '/commande/confirmation'] : self::view($menus, $selected, $old, ['form' => 'Le formulaire a expiré. Vérifiez vos informations puis confirmez à nouveau.'], null, $user);
            }

            $menu = $selected > 0 ? $menuModel->findActive($selected) : null;
            [$errors, $clean] = OrderValidator::validate($old, $menu);

            if ($errors === []) {
                $quote = (new DeliveryQuote())->forAddress($clean['adresse'], $clean['code_postal'], $clean['ville']);
                if (!$quote['ok']) {
                    $errors['adresse'] = self::QUOTE_MESSAGES[$quote['error']];
                }
            }

            if ($errors === []) {
                $placed = (new OrderModel())->place((int) $user['id'], $clean, $quote);
                if (!isset($placed['error'])) {
                    return self::completed($user, $placed, $clean);
                }
                [$field, $message] = self::REJECTIONS[$placed['error']];
                $errors[$field] = $message;
            }
        } elseif ($selected > 0 && ($menu = $menuModel->findActive($selected)) !== null) {
            $old['nombre_personne'] = (string) $menu['nombre_personne_minimum'];
        }

        return self::view($menus, $selected, $old, $errors, $quote, $user);
    }

    public static function edit(array $params, array $query): ?array
    {
        $user = Auth::user();
        $orders = new OrderModel();
        $order = $orders->findForUser((int) $params['id'], (int) $user['id']);
        if ($order === null) {
            return null;
        }

        $detail = '/mes-commandes/' . (int) $order['commande_id'];
        if ($order['statut'] !== 'en_attente') {
            Session::flash('error', 'Commande acceptée, non modifiable.');
            return ['redirect' => $detail];
        }

        $menu = (new MenuModel())->find((int) $order['menu_id']);
        $unit = intdiv(
            OrderPricing::cents($order['prix_menu']) + OrderPricing::cents($order['remise']),
            max(1, (int) $order['nombre_personne'])
        );
        $errors = [];
        $old = self::fromOrder($order);
        $quote = ['ok' => true, 'km' => (float) $order['distance_km'], 'source' => (string) $order['distance_source']];

        if (is_post()) {
            $old = self::posted();
            if (!self::nonceValid()) {
                return ['redirect' => $detail];
            }

            [$errors, $clean] = OrderValidator::validate($old, $menu, ['check_stock' => false, 'keep_date' => $order['date_prestation']]);
            $quote = null;

            if ($errors === []) {
                $quote = (new DeliveryQuote())->forAddress($clean['adresse'], $clean['code_postal'], $clean['ville']);
                if (!$quote['ok']) {
                    $errors['adresse'] = self::QUOTE_MESSAGES[$quote['error']];
                }
            }

            if ($errors === []) {
                $updated = $orders->update((int) $order['commande_id'], (int) $user['id'], $clean, $quote);
                if (!isset($updated['error'])) {
                    return self::modified($user, $order, $updated, $clean, $detail);
                }
                if ($updated['error'] === 'notfound') {
                    return null;
                }
                if ($updated['error'] === 'status') {
                    Session::flash('error', 'Commande acceptée, non modifiable.');
                    return ['redirect' => $detail];
                }
                $errors['nombre_personne'] = self::REJECTIONS['minimum'][1];
            }
        }

        return self::view([$menu], (int) $order['menu_id'], $old, $errors, $quote, $user, [
            'mode' => 'edit',
            'order' => $order,
            'action' => '/mes-commandes/' . (int) $order['commande_id'] . '/modifier',
            'unit' => $unit,
        ]);
    }

    public static function quote(array $params, array $query): array
    {
        $address = input_string($query, 'adresse');
        $postcode = input_string($query, 'code_postal');
        $city = input_string($query, 'ville');

        if (mb_strlen($address) < 5 || preg_match('/^\d{5}$/', $postcode) !== 1 || !RegistrationValidator::isCity($city)) {
            return ['status' => 422, 'json' => ['ok' => false, 'error' => 'invalid', 'message' => self::QUOTE_MESSAGES['invalid']]];
        }

        $quote = (new DeliveryQuote())->forAddress($address, $postcode, $city);
        if (!$quote['ok']) {
            return ['json' => ['ok' => false, 'error' => $quote['error'], 'message' => self::QUOTE_MESSAGES[$quote['error']]]];
        }

        return ['json' => [
            'ok' => true,
            'km' => $quote['km'],
            'source' => $quote['source'],
            'fee_cents' => OrderPricing::deliveryCents($quote['km'], $quote['source']),
        ]];
    }

    public static function confirmation(array $params, array $query): array
    {
        $id = $_SESSION['last_order_id'] ?? null;
        $order = is_int($id) ? (new OrderModel())->findForUser($id, (int) Auth::user()['id']) : null;

        if ($order === null) {
            return ['redirect' => '/menus'];
        }

        return ['vars' => ['order' => $order, 'summary' => OrderPricing::fromOrder($order), 'user' => Auth::user()]];
    }

    private static function view(array $menus, int $selected, array $old, array $errors, ?array $quote, array $user, array $extra = []): array
    {
        $_SESSION['order_nonce'] = bin2hex(random_bytes(16));
        $current = null;
        foreach ($menus as $menu) {
            if ((int) $menu['menu_id'] === $selected) {
                $current = $menu;
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => [
                'menus' => $menus,
                'selected' => $current === null ? 0 : $selected,
                'old' => $old,
                'errors' => $errors,
                'user' => $user,
                'nonce' => $_SESSION['order_nonce'],
                'summary' => $current === null ? null : self::summary($current, $old, $quote, $extra['unit'] ?? null),
                'rules' => order_config(),
            ] + $extra,
        ];
    }

    private static function summary(array $menu, array $old, ?array $quote, ?int $unit): array
    {
        $minimum = (int) $menu['nombre_personne_minimum'];
        $people = max($minimum, (int) $old['nombre_personne']);
        $pricing = OrderPricing::forUnit(
            $unit ?? OrderPricing::cents($menu['prix_par_personne']),
            $minimum,
            $people,
            $quote ?? ['km' => 0.0, 'source' => 'bordeaux']
        );

        if ($quote === null || !$quote['ok']) {
            $pricing['delivery'] = null;
            $pricing['total'] = null;
            $pricing['source'] = '';
        }

        return $pricing;
    }

    private static function completed(array $user, array $placed, array $clean): array
    {
        unset($_SESSION['order_nonce']);
        $_SESSION['last_order_id'] = $placed['id'];
        $_SESSION['last_order_at'] = time();

        $pricing = $placed['pricing'];
        Mailer::send($user['email'], 'Confirmation de votre commande ' . $placed['numero'], implode("\n", [
            'Bonjour ' . ($user['prenom'] ?? '') . ',',
            '',
            'Nous avons bien reçu votre commande ' . $placed['numero'] . ' : ' . $placed['menu']['titre'] . ' pour ' . $clean['nombre_personne'] . ' personnes.',
            'Prestation le ' . format_date($clean['date_prestation']) . ' à ' . $clean['heure_livraison'] . ', ' . $clean['adresse'] . ', ' . $clean['code_postal'] . ' ' . $clean['ville'] . '.',
            'Menu : ' . money($pricing['menu']) . ' | Livraison : ' . money($pricing['delivery']) . ' | Total : ' . money($pricing['total']),
            '',
            'Conditions du menu : ' . $placed['menu']['conditions'],
            '',
            'Notre équipe reviendra vers vous rapidement pour valider la commande. Vous pouvez la suivre sur ' . app_url('/mon-compte') . '.',
            '',
            'Julie et José',
        ]));

        return ['redirect' => '/commande/confirmation'];
    }

    private static function modified(array $user, array $order, array $updated, array $clean, string $detail): array
    {
        unset($_SESSION['order_nonce']);
        $pricing = $updated['pricing'];

        Mailer::send($user['email'], 'Modification de votre commande ' . $order['numero_commande'], implode("\n", [
            'Bonjour ' . ($user['prenom'] ?? '') . ',',
            '',
            'Votre commande ' . $order['numero_commande'] . ' (' . $updated['menu']['titre'] . ') a bien été modifiée.',
            $clean['nombre_personne'] . ' personnes, le ' . format_date($clean['date_prestation']) . ' à ' . $clean['heure_livraison'] . ', ' . $clean['adresse'] . ', ' . $clean['code_postal'] . ' ' . $clean['ville'] . '.',
            'Menu : ' . money($pricing['menu']) . ' | Livraison : ' . money($pricing['delivery']) . ' | Total : ' . money($pricing['total']),
            '',
            'Julie et José',
        ]));
        Session::flash('success', 'Votre commande a été modifiée.');

        return ['redirect' => $detail];
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
            'accept' => '',
        ];
    }

    private static function defaults(array $user): array
    {
        return [
            'telephone' => (string) ($user['telephone'] ?? ''),
            'adresse' => (string) ($user['adresse_postale'] ?? ''),
            'code_postal' => (string) ($user['code_postal'] ?? ''),
            'ville' => (string) ($user['ville'] ?? ''),
            'date_prestation' => '',
            'heure_livraison' => '12:00',
            'nombre_personne' => '',
            'accept' => '',
        ];
    }

    private static function identifier(mixed $raw): int
    {
        return is_string($raw) && preg_match('/^\d{1,9}$/', $raw) === 1 ? (int) $raw : 0;
    }

    private static function nonceValid(): bool
    {
        $expected = $_SESSION['order_nonce'] ?? '';
        $submitted = input_string($_POST, 'order_nonce', false);

        return is_string($expected) && $expected !== '' && hash_equals($expected, $submitted);
    }

    private static function recentlyOrdered(): bool
    {
        return isset($_SESSION['last_order_id']) && time() - (int) ($_SESSION['last_order_at'] ?? 0) < 120;
    }
}

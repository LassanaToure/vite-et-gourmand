<?php
declare(strict_types=1);

final class OrderPricing
{
    public static function cents(string|float|int $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    public static function deliveryCents(float $km, string $source): int
    {
        if ($source === 'bordeaux') {
            return 0;
        }

        $config = order_config();
        $tenths = (int) round($km * 10);

        return $config['delivery_fee_cents'] + (int) round($tenths * $config['per_km_cents'] / 10);
    }

    public static function compute(array $menu, int $people, array $quote): array
    {
        return self::forUnit(self::cents($menu['prix_par_personne']), (int) $menu['nombre_personne_minimum'], $people, $quote);
    }

    public static function forUnit(int $unit, int $minimum, int $people, array $quote): array
    {
        $config = order_config();
        $base = $unit * $people;
        $eligible = $people >= $minimum + $config['discount_extra_people'];
        $discount = $eligible ? intdiv($base * $config['discount_percent'] + 50, 100) : 0;
        $delivery = self::deliveryCents((float) $quote['km'], (string) $quote['source']);

        return [
            'unit' => $unit,
            'people' => $people,
            'base' => $base,
            'discount' => $discount,
            'discount_percent' => $config['discount_percent'],
            'menu' => $base - $discount,
            'km' => (float) $quote['km'],
            'source' => (string) $quote['source'],
            'delivery' => $delivery,
            'total' => $base - $discount + $delivery,
        ];
    }

    public static function fromOrder(array $order): array
    {
        $people = (int) $order['nombre_personne'];
        $menu = self::cents($order['prix_menu']);
        $discount = self::cents($order['remise']);
        $base = $menu + $discount;
        $delivery = self::cents($order['prix_livraison']);

        return [
            'unit' => intdiv($base, max(1, $people)),
            'people' => $people,
            'base' => $base,
            'discount' => $discount,
            'discount_percent' => order_config()['discount_percent'],
            'menu' => $menu,
            'km' => (float) $order['distance_km'],
            'source' => (string) $order['distance_source'],
            'delivery' => $delivery,
            'total' => $menu + $delivery,
        ];
    }
}

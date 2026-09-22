<?php
declare(strict_types=1);

final class DeliveryQuote
{
    private const CACHE_KEY = 'delivery_quotes';
    private const CACHE_LIMIT = 10;

    public function __construct(private ?Geocoder $geocoder = null)
    {
    }

    public function forAddress(string $address, string $postcode, string $city): array
    {
        $key = sha1(normalize_text($address) . '|' . $postcode . '|' . normalize_text($city));
        $cached = $this->cached($key);
        if ($cached !== null) {
            return $cached;
        }

        $quote = $this->compute($address, $postcode, $city);
        if ($quote['ok']) {
            $this->store($key, $quote);
        }

        return $quote;
    }

    private function compute(string $address, string $postcode, string $city): array
    {
        $config = order_config();

        if (normalize_text($city) === 'bordeaux' && in_array($postcode, $config['bordeaux_postcodes'], true)) {
            return self::success(0.0, 'bordeaux');
        }

        $geocoder = $this->geocoder ?? Geocoder::fromConfig();
        $point = $geocoder->locate($address, $postcode, $city);

        if ($point !== null) {
            if ($point['citycode'] === $config['bordeaux_insee']) {
                return self::success(0.0, 'bordeaux');
            }
            return $this->withinRange($point['lat'], $point['lon'], 'ban');
        }

        $communes = require BASE_PATH . '/app/data/communes.php';
        $commune = $communes[normalize_text($city)] ?? null;
        if ($commune === null) {
            return self::failure('unavailable');
        }

        return $this->withinRange($commune['lat'], $commune['lon'], 'commune');
    }

    private function withinRange(float $lat, float $lon, string $source): array
    {
        $config = order_config();
        $km = round(Distance::haversine($config['origin']['lat'], $config['origin']['lon'], $lat, $lon), 1);

        return $km > $config['max_km'] ? self::failure('too_far') : self::success($km, $source);
    }

    private function cached(string $key): ?array
    {
        $entry = $_SESSION[self::CACHE_KEY][$key] ?? null;
        if (!is_array($entry) || $entry['at'] < time() - order_config()['quote_ttl']) {
            return null;
        }

        return $entry['quote'];
    }

    private function store(string $key, array $quote): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION[self::CACHE_KEY][$key] = ['at' => time(), 'quote' => $quote];
        $_SESSION[self::CACHE_KEY] = array_slice($_SESSION[self::CACHE_KEY], -self::CACHE_LIMIT, null, true);
    }

    private static function success(float $km, string $source): array
    {
        return ['ok' => true, 'km' => $km, 'source' => $source, 'error' => null];
    }

    private static function failure(string $error): array
    {
        return ['ok' => false, 'km' => 0.0, 'source' => null, 'error' => $error];
    }
}

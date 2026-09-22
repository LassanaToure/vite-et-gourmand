<?php
declare(strict_types=1);

return [
    'delivery_fee_cents' => 500,
    'per_km_cents' => 59,
    'discount_percent' => 10,
    'discount_extra_people' => 5,
    'origin' => ['lat' => 44.8378, 'lon' => -0.5792],
    'bordeaux_insee' => '33063',
    'bordeaux_postcodes' => ['33000', '33100', '33200', '33300', '33800'],
    'geocoder_url' => getenv('GEOCODER_URL') ?: 'https://data.geopf.fr/geocodage/search',
    'geocoder_timeout' => 3,
    'geocoder_min_score' => 0.5,
    'max_km' => 150.0,
    'delivery_hours' => ['07:00', '22:00'],
    'max_days_ahead' => 365,
    'quote_ttl' => 1800,
];

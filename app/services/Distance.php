<?php
declare(strict_types=1);

final class Distance
{
    private const EARTH_RADIUS_KM = 6371.0088;

    public static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);
        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLon / 2) ** 2;

        return 2 * self::EARTH_RADIUS_KM * asin(min(1.0, sqrt($a)));
    }
}

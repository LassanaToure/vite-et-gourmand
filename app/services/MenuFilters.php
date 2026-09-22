<?php
declare(strict_types=1);

final class MenuFilters
{
    private const DECIMALS = ['prix_max', 'fourchette_min', 'fourchette_max'];
    private const INTEGERS = ['theme', 'regime', 'personnes'];

    public static function fromQuery(array $query): array
    {
        $filters = [];

        foreach (self::DECIMALS as $key) {
            $value = self::decimal($query[$key] ?? null);
            if ($value !== null) {
                $filters[$key] = $value;
            }
        }

        foreach (self::INTEGERS as $key) {
            $value = self::positiveInteger($query[$key] ?? null);
            if ($value !== null) {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }

    private static function decimal(mixed $raw): ?string
    {
        if (!is_string($raw)) {
            return null;
        }

        $value = str_replace(',', '.', trim($raw));

        return preg_match('/^\d{1,6}(\.\d{1,2})?$/', $value) === 1 ? $value : null;
    }

    private static function positiveInteger(mixed $raw): ?int
    {
        if (!is_string($raw) || preg_match('/^\d{1,9}$/', $raw) !== 1) {
            return null;
        }

        $value = (int) $raw;

        return $value > 0 ? $value : null;
    }
}

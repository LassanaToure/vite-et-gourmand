<?php
declare(strict_types=1);

final class HoursValidator
{
    private const TIME = '/^([01]\d|2[0-3]):[0-5]\d$/';

    public static function validate(array $input, array $days): array
    {
        $errors = [];
        $clean = [];

        foreach ($days as $day) {
            $id = (int) $day['horaire_id'];
            $row = is_array($input[$id] ?? null) ? $input[$id] : [];
            $closed = ($row['closed'] ?? '') === '1';
            $open = is_string($row['open'] ?? null) ? trim($row['open']) : '';
            $close = is_string($row['close'] ?? null) ? trim($row['close']) : '';

            if ($closed) {
                $clean[$id] = ['open' => null, 'close' => null];
                continue;
            }

            if (preg_match(self::TIME, $open) !== 1 || preg_match(self::TIME, $close) !== 1) {
                $errors['hours-' . $id] = $day['jour'] . ' : saisissez une heure d\'ouverture et de fermeture valides (HH:MM) ou cochez « Fermé ».';
            } elseif ($close <= $open) {
                $errors['hours-' . $id] = $day['jour'] . ' : la fermeture doit être après l\'ouverture.';
            }
            $clean[$id] = ['open' => $open . ':00', 'close' => $close . ':00'];
        }

        return [$errors, $clean];
    }
}

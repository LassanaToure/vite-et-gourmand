<?php
declare(strict_types=1);

final class AdminHoursController
{
    public static function form(array $params, array $query): array
    {
        $model = new HoraireModel();
        $days = $model->all();
        $errors = [];
        $values = self::fromRows($days);

        if (is_post()) {
            $posted = is_array($_POST['hours'] ?? null) ? $_POST['hours'] : [];
            [$errors, $clean] = HoursValidator::validate($posted, $days);
            $values = self::fromPost($posted, $days);

            if ($errors === []) {
                $model->saveAll($clean);
                Session::flash('success', 'Les horaires ont été enregistrés.');

                return ['redirect' => '/admin/horaires'];
            }
        }

        return [
            'status' => $errors === [] ? 200 : 422,
            'vars' => ['days' => $days, 'values' => $values, 'errors' => $errors],
        ];
    }

    private static function fromRows(array $days): array
    {
        $values = [];
        foreach ($days as $day) {
            $values[(int) $day['horaire_id']] = [
                'open' => $day['heure_ouverture'] === null ? '' : substr($day['heure_ouverture'], 0, 5),
                'close' => $day['heure_fermeture'] === null ? '' : substr($day['heure_fermeture'], 0, 5),
                'closed' => $day['heure_ouverture'] === null,
            ];
        }

        return $values;
    }

    private static function fromPost(array $posted, array $days): array
    {
        $values = [];
        foreach ($days as $day) {
            $id = (int) $day['horaire_id'];
            $row = is_array($posted[$id] ?? null) ? $posted[$id] : [];
            $values[$id] = [
                'open' => is_string($row['open'] ?? null) ? trim($row['open']) : '',
                'close' => is_string($row['close'] ?? null) ? trim($row['close']) : '',
                'closed' => ($row['closed'] ?? '') === '1',
            ];
        }

        return $values;
    }
}

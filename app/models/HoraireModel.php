<?php
declare(strict_types=1);

final class HoraireModel extends Model
{
    public function all(): array
    {
        return $this->pdo()->query(
            'SELECT horaire_id, jour, heure_ouverture, heure_fermeture FROM horaire ORDER BY horaire_id'
        )->fetchAll();
    }

    public function saveAll(array $hours): void
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $statement = $pdo->prepare(
                'UPDATE horaire SET heure_ouverture = :open, heure_fermeture = :close WHERE horaire_id = :id'
            );
            foreach ($hours as $id => $range) {
                $statement->execute(['open' => $range['open'], 'close' => $range['close'], 'id' => $id]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }
}

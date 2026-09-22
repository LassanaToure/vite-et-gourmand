<?php
declare(strict_types=1);

final class PasswordResetModel extends Model
{
    public function create(int $userId, string $tokenHash): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO password_reset (utilisateur_id, token_hash, expire_le)
            VALUES (:user, :hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        );
        $statement->execute(['user' => $userId, 'hash' => $tokenHash]);
    }

    public function findValid(string $tokenHash): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT pr.reset_id, pr.utilisateur_id
            FROM password_reset pr
            JOIN utilisateur u ON u.utilisateur_id = pr.utilisateur_id
            WHERE pr.token_hash = :hash AND pr.utilise = 0 AND pr.expire_le > NOW() AND u.actif = 1'
        );
        $statement->execute(['hash' => $tokenHash]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function invalidateForUser(int $userId): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE password_reset SET utilise = 1 WHERE utilisateur_id = :user AND utilise = 0'
        );
        $statement->execute(['user' => $userId]);
    }
}

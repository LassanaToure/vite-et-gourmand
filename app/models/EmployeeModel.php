<?php
declare(strict_types=1);

final class EmployeeModel extends Model
{
    public function list(): array
    {
        return $this->pdo()->query(
            "SELECT u.utilisateur_id AS id, u.email, u.nom, u.prenom, u.actif, u.date_creation
            FROM utilisateur u
            JOIN role r ON r.role_id = u.role_id
            WHERE r.libelle = 'employe'
            ORDER BY u.actif DESC, u.email"
        )->fetchAll();
    }

    public function create(string $email, string $passwordHash): int
    {
        $statement = $this->pdo()->prepare(
            "INSERT INTO utilisateur (email, password, role_id)
            VALUES (:email, :password, (SELECT role_id FROM role WHERE libelle = 'employe'))"
        );
        $statement->execute(['email' => $email, 'password' => $passwordHash]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function setActive(int $id, bool $active): bool
    {
        $statement = $this->pdo()->prepare(
            "UPDATE utilisateur SET actif = :actif
            WHERE utilisateur_id = :id
                AND role_id = (SELECT role_id FROM role WHERE libelle = 'employe')"
        );
        $statement->execute(['actif' => (int) $active, 'id' => $id]);

        return $statement->rowCount() === 1;
    }
}

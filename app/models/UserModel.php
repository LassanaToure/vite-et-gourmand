<?php
declare(strict_types=1);

final class UserModel extends Model
{
    private const COLUMNS = 'u.utilisateur_id AS id, u.email, u.nom, u.prenom, u.telephone, u.ville, u.pays,
        u.adresse_postale, u.code_postal, u.actif, r.libelle AS role';

    private const JOINS = 'FROM utilisateur u JOIN role r ON r.role_id = u.role_id';

    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT ' . self::COLUMNS . ' ' . self::JOINS . ' WHERE u.utilisateur_id = :id'
        );
        $statement->execute(['id' => $id]);

        return $this->hydrate($statement->fetch());
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT ' . self::COLUMNS . ', u.password ' . self::JOINS . ' WHERE u.email = :email'
        );
        $statement->execute(['email' => $email]);

        return $this->hydrate($statement->fetch());
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->pdo()->prepare('SELECT 1 FROM utilisateur WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        return $statement->fetchColumn() !== false;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            "INSERT INTO utilisateur (email, password, nom, prenom, telephone, ville, adresse_postale, code_postal, role_id)
            VALUES (:email, :password, :nom, :prenom, :telephone, :ville, :adresse, :code_postal,
                (SELECT role_id FROM role WHERE libelle = 'utilisateur'))"
        );
        $statement->execute([
            'email' => $data['email'],
            'password' => $data['password_hash'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'],
            'ville' => $data['ville'],
            'adresse' => $data['adresse'],
            'code_postal' => $data['code_postal'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE utilisateur
            SET nom = :nom, prenom = :prenom, telephone = :telephone, adresse_postale = :adresse,
                code_postal = :code_postal, ville = :ville
            WHERE utilisateur_id = :id'
        );
        $statement->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'],
            'adresse' => $data['adresse'],
            'code_postal' => $data['code_postal'],
            'ville' => $data['ville'],
            'id' => $id,
        ]);
    }

    public function passwordHash(int $id): ?string
    {
        $statement = $this->pdo()->prepare('SELECT password FROM utilisateur WHERE utilisateur_id = :id');
        $statement->execute(['id' => $id]);
        $hash = $statement->fetchColumn();

        return $hash === false ? null : (string) $hash;
    }

    public function updatePassword(int $id, string $hash): void
    {
        $statement = $this->pdo()->prepare('UPDATE utilisateur SET password = :password WHERE utilisateur_id = :id');
        $statement->execute(['password' => $hash, 'id' => $id]);
    }

    private function hydrate(array|false $row): ?array
    {
        if ($row === false) {
            return null;
        }
        $row['id'] = (int) $row['id'];
        $row['actif'] = (bool) $row['actif'];

        return $row;
    }
}

<?php
declare(strict_types=1);

final class ReferenceModel extends Model
{
    public function themes(): array
    {
        return $this->pdo()->query('SELECT theme_id AS id, libelle FROM theme ORDER BY libelle')->fetchAll();
    }

    public function regimes(): array
    {
        return $this->pdo()->query('SELECT regime_id AS id, libelle FROM regime ORDER BY libelle')->fetchAll();
    }

    public function allergenes(): array
    {
        return $this->pdo()->query('SELECT allergene_id AS id, libelle FROM allergene ORDER BY libelle')->fetchAll();
    }
}

<?php
declare(strict_types=1);

abstract class Model
{
    public function __construct(private ?PDO $pdo = null)
    {
    }

    protected function pdo(): PDO
    {
        return $this->pdo ??= db();
    }
}

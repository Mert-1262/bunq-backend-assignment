<?php

declare(strict_types=1);

namespace App\Db;

use PDO;

final class Connection
{
    private PDO $pdo;

    public function __construct(string $dbPath)
    {
        $this->pdo = new PDO(
            "sqlite:" . $dbPath,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        $this->pdo->exec("PRAGMA foreign_keys = ON;");
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}

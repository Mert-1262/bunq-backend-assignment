<?php

declare(strict_types=1);

namespace App\Repository;

use App\Db\Connection;
use App\Domain\User\User;

final class UserRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->connection->getPdo()->prepare(
            'SELECT id, username, created_at 
             FROM users 
             WHERE username = :username 
             LIMIT 1'
        );

        $stmt->execute([
            'username' => $username
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return new User(
            (int) $row['id'],
            (string) $row['username'],
            (string) $row['created_at']
        );
    }

    public function create(string $username): User
    {
        $now = gmdate('c');

        $stmt = $this->connection->getPdo()->prepare(
            'INSERT INTO users (username, created_at) 
             VALUES (:username, :created_at)'
        );

        $stmt->execute([
            'username'   => $username,
            'created_at'=> $now
        ]);

        $id = (int) $this->connection->getPdo()->lastInsertId();

        return new User(
            $id,
            $username,
            $now
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Db\Connection;
use App\Domain\Group\Group;

final class GroupRepository
{
    public function __construct(private Connection $connection) {}

    public function listAll(): array
    {
        $stmt = $this->connection->getPdo()->query(
            'SELECT id, name, created_at FROM groups ORDER BY id DESC'
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $out = [];

        foreach ($rows as $r) {
            $out[] = new Group((int)$r['id'], (string)$r['name'], (string)$r['created_at']);
        }

        return $out;
    }

    public function findById(int $id): ?Group
    {
        $stmt = $this->connection->getPdo()->prepare(
            'SELECT id, name, created_at FROM groups WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r === false) {
            return null;
        }

        return new Group((int)$r['id'], (string)$r['name'], (string)$r['created_at']);
    }

    public function create(string $name): Group
    {
        $now = gmdate('c');

        $stmt = $this->connection->getPdo()->prepare(
            'INSERT INTO groups (name, created_at) VALUES (:name, :created_at)'
        );

        $stmt->execute([
            'name' => $name,
            'created_at' => $now
        ]);

        $id = (int)$this->connection->getPdo()->lastInsertId();

        return new Group($id, $name, $now);
    }
}

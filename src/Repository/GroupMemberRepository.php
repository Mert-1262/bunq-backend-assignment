<?php

declare(strict_types=1);

namespace App\Repository;

use App\Db\Connection;

final class GroupMemberRepository
{
    public function __construct(private Connection $connection) {}

    public function isMember(int $groupId, int $userId): bool
    {
        $stmt = $this->connection->getPdo()->prepare(
            'SELECT 1 FROM group_members WHERE group_id = :g AND user_id = :u LIMIT 1'
        );
        $stmt->execute(['g' => $groupId, 'u' => $userId]);

        $r = $stmt->fetchColumn();
        if ($r === false) {
            return false;
        }
        return true;
    }

    public function join(int $groupId, int $userId): void
    {
        $now = gmdate('c');

        $stmt = $this->connection->getPdo()->prepare(
            'INSERT INTO group_members (group_id, user_id, joined_at) VALUES (:g, :u, :j)'
        );

        $stmt->execute([
            'g' => $groupId,
            'u' => $userId,
            'j' => $now
        ]);
    }
}

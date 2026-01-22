<?php

declare(strict_types=1);

namespace App\Repository;

use App\Db\Connection;
use App\Domain\Message\Message;

final class MessageRepository
{
    public function __construct(private Connection $connection) {}

    public function create(int $groupId, int $userId, string $body): Message
    {
        $now = gmdate('c');

        $stmt = $this->connection->getPdo()->prepare(
            'INSERT INTO messages (group_id, user_id, body, created_at)
             VALUES (:g, :u, :b, :c)'
        );

        $stmt->execute([
            'g' => $groupId,
            'u' => $userId,
            'b' => $body,
            'c' => $now
        ]);

        $id = (int)$this->connection->getPdo()->lastInsertId();

        $stmt2 = $this->connection->getPdo()->prepare(
            'SELECT m.id, m.group_id, m.user_id, u.username, m.body, m.created_at
             FROM messages m
             JOIN users u ON u.id = m.user_id
             WHERE m.id = :id
             LIMIT 1'
        );
        $stmt2->execute(['id' => $id]);
        $r = $stmt2->fetch(\PDO::FETCH_ASSOC);

        return new Message(
            (int)$r['id'],
            (int)$r['group_id'],
            (int)$r['user_id'],
            (string)$r['username'],
            (string)$r['body'],
            (string)$r['created_at']
        );
    }

    public function listByGroup(int $groupId, int $limit, ?int $beforeId): array
    {
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $sql = '
            SELECT m.id, m.group_id, m.user_id, u.username, m.body, m.created_at
            FROM messages m
            JOIN users u ON u.id = m.user_id
            WHERE m.group_id = :g
        ';

        $params = ['g' => $groupId];

        if ($beforeId !== null) {
            $sql .= ' AND m.id < :beforeId ';
            $params['beforeId'] = $beforeId;
        }

        $sql .= ' ORDER BY m.id DESC LIMIT :lim ';

        $stmt = $this->connection->getPdo()->prepare($sql);
        $stmt->bindValue(':g', $groupId, \PDO::PARAM_INT);

        if ($beforeId !== null) {
            $stmt->bindValue(':beforeId', $beforeId, \PDO::PARAM_INT);
        }

        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Message(
                (int)$r['id'],
                (int)$r['group_id'],
                (int)$r['user_id'],
                (string)$r['username'],
                (string)$r['body'],
                (string)$r['created_at']
            );
        }

        return $out;
    }
}

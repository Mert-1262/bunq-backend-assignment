<?php

declare(strict_types=1);

namespace App\Domain\Message;

final class Message
{
    public function __construct(
        private int $id,
        private int $groupId,
        private int $userId,
        private string $username,
        private string $body,
        private string $createdAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'groupId' => $this->groupId,
            'userId' => $this->userId,
            'username' => $this->username,
            'body' => $this->body,
            'createdAt' => $this->createdAt,
        ];
    }
}

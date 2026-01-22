<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Message\Message;
use App\Repository\GroupMemberRepository;
use App\Repository\GroupRepository;
use App\Repository\MessageRepository;

final class MessageService
{
    public function __construct(
        private GroupRepository $groups,
        private GroupMemberRepository $members,
        private MessageRepository $messages
    ) {}

    public function sendMessage(int $groupId, int $userId, string $body): Message
    {
        $g = $this->groups->findById($groupId);
        if ($g === null) {
            throw new \RuntimeException('group_not_found');
        }

        $isMember = $this->members->isMember($groupId, $userId);
        if ($isMember === false) {
            throw new \RuntimeException('not_a_member');
        }

        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('body is required');
        }
        if (mb_strlen($body) > 2000) {
            throw new \InvalidArgumentException('body is too long');
        }

        return $this->messages->create($groupId, $userId, $body);
    }

    public function listMessages(int $groupId, int $userId, int $limit, ?int $beforeId): array
    {
        $g = $this->groups->findById($groupId);
        if ($g === null) {
            throw new \RuntimeException('group_not_found');
        }

        $isMember = $this->members->isMember($groupId, $userId);
        if ($isMember === false) {
            throw new \RuntimeException('not_a_member');
        }

        return $this->messages->listByGroup($groupId, $limit, $beforeId);
    }
}

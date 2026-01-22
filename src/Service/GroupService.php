<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Group\Group;
use App\Repository\GroupMemberRepository;
use App\Repository\GroupRepository;

final class GroupService
{
    public function __construct(
        private GroupRepository $groups,
        private GroupMemberRepository $members
    ) {}

    public function listGroups(): array
    {
        return $this->groups->listAll();
    }

    public function createGroup(string $name): Group
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('name is required');
        }
        if (mb_strlen($name) > 100) {
            throw new \InvalidArgumentException('name is too long');
        }

        return $this->groups->create($name);
    }

    public function joinGroup(int $groupId, int $userId): void
    {
        $g = $this->groups->findById($groupId);
        if ($g === null) {
            throw new \RuntimeException('group_not_found');
        }

        $already = $this->members->isMember($groupId, $userId);
        if ($already) {
            return;
        }

        $this->members->join($groupId, $userId);
    }
}

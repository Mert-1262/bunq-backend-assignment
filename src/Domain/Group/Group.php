<?php

declare(strict_types=1);

namespace App\Domain\Group;

final class Group
{
    public function __construct(
        private int $id,
        private string $name,
        private string $createdAt
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}

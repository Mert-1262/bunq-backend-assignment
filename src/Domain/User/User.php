<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

final class User implements JsonSerializable
{
    private int $id;
    private string $username;
    private string $createdAt;
    private string $firstName;
    private string $lastName;

    public function __construct(int $id, string $username, string $third, string $fourth = '', string $fifth = '')
    {
        $this->id = $id;
        $this->username = $username;

        // Support two constructor shapes:
        // 1) (id, username, createdAt)
        // 2) (id, username, firstName, lastName [, createdAt])

        $argc = func_num_args();

        if ($argc === 3) {
            // (id, username, createdAt)
            $this->createdAt = $third;
            $this->firstName = '';
            $this->lastName = '';
            return;
        }

        // (id, username, firstName, lastName [, createdAt])
        $this->firstName = $third;
        $this->lastName = $fourth;
        $this->createdAt = $fifth;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\User\User;
use App\Repository\UserRepository;

final class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function getOrCreate(string $username): User
    {
        $username = trim($username);

        if ($username === '') {
            throw new \InvalidArgumentException('username is required');
        }

        $user = $this->userRepository->findByUsername($username);
        if ($user !== null) {
            return $user;
        }

        return $this->userRepository->create($username);
    }
}

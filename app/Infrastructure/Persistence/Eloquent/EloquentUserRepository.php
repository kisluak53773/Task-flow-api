<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent;

use Domain\User\Repository\UserRepositoryInterface;
use Domain\User\Model\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
}

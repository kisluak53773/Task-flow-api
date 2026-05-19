<?php

declare(strict_types=1);

namespace Domain\User\Repository;

use Domain\User\Model\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function delete(User $user): bool;
}

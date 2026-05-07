<?php

declare(strict_types=1);

namespace Domain\Project\Repository;

use Domain\Project\Model\Project;
use Domain\User\Model\User;

interface ProjectRepositoryInterface
{
    public function createWithMember(array $data, User $user, string $role): Project;
}

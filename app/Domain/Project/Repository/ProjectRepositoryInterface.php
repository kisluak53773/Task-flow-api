<?php

declare(strict_types=1);

namespace Domain\Project\Repository;

use Domain\Project\Model\Project;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;

interface ProjectRepositoryInterface
{
    public function createWithMember(array $data, User $user, string $role): Project;

    public function findById(int|string $id): ?Project;

    public function  hasMemeBer(Project $project ,User $user): bool;

    public function addMember(Project $project, User $user, ProjectRole $role): void;

    public function  delete(Project $project): void;
}

<?php

declare(strict_types=1);

namespace Infrastructure\Security;

use Domain\Project\Model\Project;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;

final class  ProjectPolicy
{
    public function invite(User $user, Project $project): bool
    {
        return $project->users()
            ->where('user_id', $user->id)
            ->where('project_user.role', ProjectRole::MEMBER->value)
            ->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->users()
            ->where('user_id', $user->id)
            ->where('project_user.role', ProjectRole::OWNER->value)
            ->exists();
    }
}

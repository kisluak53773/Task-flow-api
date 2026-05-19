<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent;

use Domain\Project\Repository\ProjectRepositoryInterface;
use Domain\Project\Model\Project;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Illuminate\Support\Facades\DB;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function createWithMember(array $data, User $user, string $role): Project
    {
        return DB::transaction(function () use ($data, $user, $role) {
            $project = Project::create($data);
            $project->users()->attach($user->id, ['role' => $role]);

            return $project;
        });
    }

    public function findById(int|string $id): ?Project
    {
        return Project::find($id);
    }

    public function hasMemeBer(Project $project, User $user): bool
    {
        return $project->users()->where('user_id', $user->id)->exists();
    }

    public function addMember(Project $project, User $user, ProjectRole $role): void
    {
        $project->users()->attach($user->id, ['role' => $role->value]);
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}

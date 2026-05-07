<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent;

use Domain\Project\Repository\ProjectRepositoryInterface;
use Domain\Project\Model\Project;
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
}

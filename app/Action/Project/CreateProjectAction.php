<?php

declare(strict_types=1);

namespace Action\Project;

use App\Dto\ProjectDto;
use Domain\Project\Model\Project;
use Domain\User\Model\User;
use Illuminate\Support\Facades\DB;
use Domain\Project\ValueObject\ProjectRole;

class CreateProjectAction
{
    public function execute(ProjectDto $data, User $creator): Project
    {
        return DB::transaction(function () use ($data, $creator) {
            $project = Project::create([
                'name' => $data->name,
                'description' => $data->description,
            ]);

            $project->users()->attach($creator->id, ['role' => ProjectRole::OWNER->value]);

            return $project;
        });
    }
}

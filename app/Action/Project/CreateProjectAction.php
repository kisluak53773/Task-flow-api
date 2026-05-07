<?php

declare(strict_types=1);

namespace Action\Project;

use App\Dto\ProjectDto;
use Domain\Project\Model\Project;
use Domain\Project\Repository\ProjectRepositoryInterface;
use Domain\User\Model\User;
use Domain\Project\ValueObject\ProjectRole;

class CreateProjectAction
{
    public function __construct(private ProjectRepositoryInterface $repository) {}

    public function execute(ProjectDto $data, User $creator): Project
    {
        $data = [
            'name' => $data->name,
            'description' => $data->description,
        ];

        return $this->repository->createWithMember($data, $creator, ProjectRole::OWNER->value);
    }
}

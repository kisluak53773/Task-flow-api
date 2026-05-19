<?php

declare(strict_types=1);

namespace Action\Project;

use Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Response;
use App\Dto\Project\DeleteProjectDto;

class DeleteProjectAction
{
    public function __construct(private ProjectRepositoryInterface $projectRepository)
    {

    }

    public function execute(DeleteProjectDto $dto): void
    {
        $project = $this->projectRepository->findById($dto->id);

        if (!$project) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Such project does not exist.');
        }

        $this->projectRepository->delete($project);
    }
}

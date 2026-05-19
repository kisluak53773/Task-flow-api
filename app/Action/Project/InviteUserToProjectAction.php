<?php

declare(strict_types=1);

namespace Action\Project;

use App\Dto\Project\InviteUserToProjectDto;
use Domain\Project\Repository\ProjectRepositoryInterface;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Repository\UserRepositoryInterface;
use Infrastructure\Mail\ProjectInvitationMail;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class InviteUserToProjectAction
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        private UserRepositoryInterface    $userRepository
    )
    {
    }

    public function execute(InviteUserToProjectDto $dto): void
    {
        $project = $this->projectRepository->findById($dto->projectId);

        if (!$project) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Project not found.');
        }

        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'User not found.');
        }

        if ($this->projectRepository->hasMemeBer($project, $user)) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'User is already a member of this project.');
        }

        $projectRole = ProjectRole::from($dto->role);

        $this->projectRepository->addMember($project, $user, $projectRole);

        Mail::to($user->email)->send(new ProjectInvitationMail($project, $user));
    }
}

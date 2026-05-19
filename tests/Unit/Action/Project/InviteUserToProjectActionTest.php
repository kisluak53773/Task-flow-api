<?php

declare(strict_types=1);

namespace Tests\Unit\Action\Project;

use Action\Project\InviteUserToProjectAction;
use App\Dto\Project\InviteUserToProjectDto;
use Domain\Project\Model\Project;
use Domain\Project\Repository\ProjectRepositoryInterface;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Domain\User\Repository\UserRepositoryInterface;
use Infrastructure\Mail\ProjectInvitationMail;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class InviteUserToProjectActionTest extends TestCase
{
    private function makeDto(string $projectId = '1', string $email = 'user@example.com', string $role = 'member'): InviteUserToProjectDto
    {
        return new InviteUserToProjectDto(
            projectId: $projectId,
            email: $email,
            role: $role,
        );
    }

    public function test_execute_throws_404_when_project_not_found(): void
    {
        /** @var ProjectRepositoryInterface&MockInterface $projectRepo */
        $projectRepo = Mockery::mock(ProjectRepositoryInterface::class);
        $projectRepo->shouldReceive('findById')->with('99')->andReturn(null);

        /** @var UserRepositoryInterface&MockInterface $userRepo */
        $userRepo = Mockery::mock(UserRepositoryInterface::class);

        $dto = $this->makeDto(projectId: '99');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Project not found.');

        $action = new InviteUserToProjectAction($projectRepo, $userRepo);
        $action->execute($dto);
    }

    public function test_execute_throws_404_when_user_not_found(): void
    {
        $project = Mockery::mock(Project::class);

        /** @var ProjectRepositoryInterface&MockInterface $projectRepo */
        $projectRepo = Mockery::mock(ProjectRepositoryInterface::class);
        $projectRepo->shouldReceive('findById')->andReturn($project);

        /** @var UserRepositoryInterface&MockInterface $userRepo */
        $userRepo = Mockery::mock(UserRepositoryInterface::class);
        $userRepo->shouldReceive('findByEmail')->with('ghost@example.com')->andReturn(null);

        $dto = $this->makeDto(email: 'ghost@example.com');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('User not found.');

        $action = new InviteUserToProjectAction($projectRepo, $userRepo);
        $action->execute($dto);
    }

    public function test_execute_throws_422_when_user_is_already_a_member(): void
    {
        $project = Mockery::mock(Project::class);
        $user = Mockery::mock(User::class);

        /** @var ProjectRepositoryInterface&MockInterface $projectRepo */
        $projectRepo = Mockery::mock(ProjectRepositoryInterface::class);
        $projectRepo->shouldReceive('findById')->andReturn($project);
        $projectRepo->shouldReceive('hasMemeBer')->with($project, $user)->andReturn(true);

        /** @var UserRepositoryInterface&MockInterface $userRepo */
        $userRepo = Mockery::mock(UserRepositoryInterface::class);
        $userRepo->shouldReceive('findByEmail')->andReturn($user);

        $dto = $this->makeDto();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('User is already a member of this project.');

        $action = new InviteUserToProjectAction($projectRepo, $userRepo);
        $action->execute($dto);
    }

    public function test_execute_adds_member_and_sends_invitation_mail(): void
    {
        Mail::fake();

        $project = Mockery::mock(Project::class);
        $project->shouldReceive('getAttribute')->andReturnUsing(fn ($key) => match ($key) {
            'name' => 'Test Project',
            default => null,
        });

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->andReturnUsing(fn ($key) => match ($key) {
            'email' => 'invitee@example.com',
            default => null,
        });

        /** @var ProjectRepositoryInterface&MockInterface $projectRepo */
        $projectRepo = Mockery::mock(ProjectRepositoryInterface::class);
        $projectRepo->shouldReceive('findById')->andReturn($project);
        $projectRepo->shouldReceive('hasMemeBer')->andReturn(false);
        $projectRepo->shouldReceive('addMember')
            ->once()
            ->withArgs(fn ($p, $u, $role) => $p === $project && $u === $user && $role === ProjectRole::MEMBER);

        /** @var UserRepositoryInterface&MockInterface $userRepo */
        $userRepo = Mockery::mock(UserRepositoryInterface::class);
        $userRepo->shouldReceive('findByEmail')->andReturn($user);

        $dto = $this->makeDto(role: ProjectRole::MEMBER->value);

        $action = new InviteUserToProjectAction($projectRepo, $userRepo);
        $action->execute($dto);

        Mail::assertQueued(ProjectInvitationMail::class, function (ProjectInvitationMail $mail) use ($project, $user) {
            return $mail->project === $project && $mail->user === $user;
        });
    }

    public function test_execute_throws_404_with_correct_http_status_code_for_missing_project(): void
    {
        /** @var ProjectRepositoryInterface&MockInterface $projectRepo */
        $projectRepo = Mockery::mock(ProjectRepositoryInterface::class);
        $projectRepo->shouldReceive('findById')->andReturn(null);

        /** @var UserRepositoryInterface&MockInterface $userRepo */
        $userRepo = Mockery::mock(UserRepositoryInterface::class);

        $dto = $this->makeDto();

        try {
            $action = new InviteUserToProjectAction($projectRepo, $userRepo);
            $action->execute($dto);
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_execute_throws_422_with_correct_http_status_code_for_existing_member(): void
    {
        $project = Mockery::mock(Project::class);
        $user = Mockery::mock(User::class);

        /** @var ProjectRepositoryInterface&MockInterface $projectRepo */
        $projectRepo = Mockery::mock(ProjectRepositoryInterface::class);
        $projectRepo->shouldReceive('findById')->andReturn($project);
        $projectRepo->shouldReceive('hasMemeBer')->andReturn(true);

        /** @var UserRepositoryInterface&MockInterface $userRepo */
        $userRepo = Mockery::mock(UserRepositoryInterface::class);
        $userRepo->shouldReceive('findByEmail')->andReturn($user);

        $dto = $this->makeDto();

        try {
            $action = new InviteUserToProjectAction($projectRepo, $userRepo);
            $action->execute($dto);
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }
}

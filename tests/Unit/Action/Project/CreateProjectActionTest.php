<?php

declare(strict_types=1);

namespace Tests\Unit\Action\Project;

use Action\Project\CreateProjectAction;
use App\Dto\Project\ProjectDto;
use Domain\Project\Model\Project;
use Domain\Project\Repository\ProjectRepositoryInterface;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateProjectActionTest extends TestCase
{
    public function test_execute_creates_project_with_owner_role_and_returns_it(): void
    {
        $dto = new ProjectDto('Alpha Project', 'First project');
        $creator = Mockery::mock(User::class);
        $expectedProject = Mockery::mock(Project::class);

        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('createWithMember')
            ->once()
            ->withArgs(function (array $data, User $user, string $role) use ($dto, $creator) {
                return $data['name'] === $dto->name
                    && $data['description'] === $dto->description
                    && $user === $creator
                    && $role === ProjectRole::OWNER->value;
            })
            ->andReturn($expectedProject);

        $action = new CreateProjectAction($repository);
        $result = $action->execute($dto, $creator);

        $this->assertSame($expectedProject, $result);
    }

    public function test_execute_passes_null_description_when_not_provided(): void
    {
        $dto = new ProjectDto('Beta Project');
        $creator = Mockery::mock(User::class);
        $capturedData = [];

        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('createWithMember')
            ->once()
            ->withArgs(function (array $data) use (&$capturedData) {
                $capturedData = $data;
                return true;
            })
            ->andReturn(Mockery::mock(Project::class));

        $action = new CreateProjectAction($repository);
        $action->execute($dto, $creator);

        $this->assertSame('Beta Project', $capturedData['name']);
        $this->assertNull($capturedData['description']);
    }

    public function test_execute_always_assigns_owner_role_to_creator(): void
    {
        $dto = new ProjectDto('Gamma Project', 'Some description');
        $creator = Mockery::mock(User::class);
        $capturedRole = null;

        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('createWithMember')
            ->once()
            ->withArgs(function (array $data, User $user, string $role) use (&$capturedRole) {
                $capturedRole = $role;
                return true;
            })
            ->andReturn(Mockery::mock(Project::class));

        $action = new CreateProjectAction($repository);
        $action->execute($dto, $creator);

        $this->assertSame(ProjectRole::OWNER->value, $capturedRole);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Action\Project;

use Action\Project\DeleteProjectAction;
use App\Dto\Project\DeleteProjectDto;
use Domain\Project\Model\Project;
use Domain\Project\Repository\ProjectRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DeleteProjectActionTest extends TestCase
{
    public function test_execute_deletes_the_project_when_found(): void
    {
        $project = Mockery::mock(Project::class);

        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('findById')->with('1')->andReturn($project);
        $repository->shouldReceive('delete')->once()->with($project);

        $dto = DeleteProjectDto::fromId('1');

        $action = new DeleteProjectAction($repository);
        $action->execute($dto);
    }

    public function test_execute_throws_404_when_project_not_found(): void
    {
        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('findById')->with('99')->andReturn(null);
        $repository->shouldReceive('delete')->never();

        $dto = DeleteProjectDto::fromId('99');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Such project does not exist.');

        $action = new DeleteProjectAction($repository);
        $action->execute($dto);
    }

    public function test_execute_throws_404_with_correct_status_code(): void
    {
        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('findById')->andReturn(null);

        $dto = DeleteProjectDto::fromId('55');

        try {
            $action = new DeleteProjectAction($repository);
            $action->execute($dto);
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_execute_does_not_call_delete_when_project_is_not_found(): void
    {
        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('findById')->andReturn(null);
        $repository->shouldReceive('delete')->never();

        $dto = DeleteProjectDto::fromId('77');

        try {
            $action = new DeleteProjectAction($repository);
            $action->execute($dto);
        } catch (HttpException) {
        }
    }

    public function test_execute_returns_void(): void
    {
        $project = Mockery::mock(Project::class);

        /** @var ProjectRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('findById')->andReturn($project);
        $repository->shouldReceive('delete');

        $dto = DeleteProjectDto::fromId('1');

        $action = new DeleteProjectAction($repository);
        $result = $action->execute($dto);

        $this->assertNull($result);
    }
}

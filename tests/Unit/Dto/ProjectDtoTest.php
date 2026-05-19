<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Project\ProjectDto;
use Mockery;
use Presentation\Api\Request\Project\StoreProjectRequest;
use Tests\TestCase;

class ProjectDtoTest extends TestCase
{
    public function test_constructor_assigns_name_and_description(): void
    {
        $dto = new ProjectDto('My Project', 'A great project');

        $this->assertSame('My Project', $dto->name);
        $this->assertSame('A great project', $dto->description);
    }

    public function test_description_defaults_to_null(): void
    {
        $dto = new ProjectDto('My Project');

        $this->assertSame('My Project', $dto->name);
        $this->assertNull($dto->description);
    }

    public function test_from_request_creates_dto_with_correct_values(): void
    {
        $request = Mockery::mock(StoreProjectRequest::class);
        $request->shouldReceive('validated')->with('name')->andReturn('Task Manager');
        $request->shouldReceive('validated')->with('description')->andReturn('Manage your tasks efficiently');

        $dto = ProjectDto::fromRequest($request);

        $this->assertInstanceOf(ProjectDto::class, $dto);
        $this->assertSame('Task Manager', $dto->name);
        $this->assertSame('Manage your tasks efficiently', $dto->description);
    }

    public function test_from_request_allows_null_description(): void
    {
        $request = Mockery::mock(StoreProjectRequest::class);
        $request->shouldReceive('validated')->with('name')->andReturn('Task Manager');
        $request->shouldReceive('validated')->with('description')->andReturn(null);

        $dto = ProjectDto::fromRequest($request);

        $this->assertNull($dto->description);
    }
}

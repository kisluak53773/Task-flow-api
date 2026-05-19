<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Project\InviteUserToProjectDto;
use Domain\Project\ValueObject\ProjectRole;
use Mockery;
use Presentation\Api\Request\Project\InviteUserRequest;
use Tests\TestCase;

class InviteUserToProjectDtoTest extends TestCase
{
    public function test_constructor_assigns_all_properties(): void
    {
        $dto = new InviteUserToProjectDto(
            projectId: 5,
            email: 'invitee@example.com',
            role: ProjectRole::MEMBER->value,
        );

        $this->assertSame(5, $dto->projectId);
        $this->assertSame('invitee@example.com', $dto->email);
        $this->assertSame(ProjectRole::MEMBER->value, $dto->role);
    }

    public function test_from_reqeust_creates_dto_with_correct_values(): void
    {
        $request = Mockery::mock(InviteUserRequest::class);
        $request->shouldReceive('validated')->with('email')->andReturn('member@example.com');
        $request->shouldReceive('validated')->with('role')->andReturn(ProjectRole::OWNER->value);

        $dto = InviteUserToProjectDto::fromReqeust($request, '12');

        $this->assertInstanceOf(InviteUserToProjectDto::class, $dto);
        $this->assertSame('12', $dto->projectId);
        $this->assertSame('member@example.com', $dto->email);
        $this->assertSame(ProjectRole::OWNER->value, $dto->role);
    }
}

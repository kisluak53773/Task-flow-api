<?php

declare(strict_types=1);

namespace  App\Dto\Project;

use Presentation\Api\Request\Project\InviteUserRequest;

final readonly class  InviteUserToProjectDto
{
    public function __construct(public int|string $projectId, public string $email,public string $role)
    {
    }

    public static function fromReqeust(InviteUserRequest $request, string $id): self
    {
        return new self(
            projectId: $id,
            email: $request->validated('email'),
            role: $request->validated('role')
        );
    }
}

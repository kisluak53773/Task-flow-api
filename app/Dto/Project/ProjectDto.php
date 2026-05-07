<?php

declare(strict_types=1);

namespace App\Dto\Project;

use Presentation\Api\Request\Project\StoreProjectRequest;

readonly class ProjectDto
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}

    public static function fromRequest(StoreProjectRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
        );
    }
}

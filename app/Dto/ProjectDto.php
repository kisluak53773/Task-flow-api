<?php

declare(strict_types=1);

namespace App\Dto;

use Presentation\Api\Request\StoreProjectRequest;

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

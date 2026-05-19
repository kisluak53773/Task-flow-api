<?php

declare(strict_types=1);

namespace App\Dto\Project;

final  readonly class DeleteProjectDto
{
    public function __construct(public string|int $id)
    {

    }

    public static function fromId(string|int $id): self
    {
        return new self(
            id: $id
        );
    }
}

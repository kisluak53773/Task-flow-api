<?php

declare(strict_types=1);

namespace App\Dto\User;

use Domain\User\Model\User;

readonly class UserOutputDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            created_at: $user->created_at?->toIso8601String(),
            updated_at: $user->updated_at?->toIso8601String(),
        );
    }
}

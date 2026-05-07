<?php

declare(strict_types=1);

namespace App\Dto\User;

use Presentation\Api\Request\User\RegsiterUserRequest;

readonly class RegisterUserDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(RegsiterUserRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        );
    }
}

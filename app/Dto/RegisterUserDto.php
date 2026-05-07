<?php

declare(strict_types=1);

namespace App\Dto;

use Presentation\Api\Request\RegsiterUserRequest;

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

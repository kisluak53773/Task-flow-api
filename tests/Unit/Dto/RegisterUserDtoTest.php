<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\User\RegisterUserDto;
use Mockery;
use Presentation\Api\Request\User\RegsiterUserRequest;
use Tests\TestCase;

class RegisterUserDtoTest extends TestCase
{
    public function test_constructor_assigns_all_properties(): void
    {
        $dto = new RegisterUserDto('John Doe', 'john@example.com', 'secret123');

        $this->assertSame('John Doe', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertSame('secret123', $dto->password);
    }

    public function test_from_request_creates_dto_with_correct_values(): void
    {
        $request = Mockery::mock(RegsiterUserRequest::class);
        $request->shouldReceive('validated')->with('name')->andReturn('Jane Doe');
        $request->shouldReceive('validated')->with('email')->andReturn('jane@example.com');
        $request->shouldReceive('validated')->with('password')->andReturn('mypassword');

        $dto = RegisterUserDto::fromRequest($request);

        $this->assertInstanceOf(RegisterUserDto::class, $dto);
        $this->assertSame('Jane Doe', $dto->name);
        $this->assertSame('jane@example.com', $dto->email);
        $this->assertSame('mypassword', $dto->password);
    }
}

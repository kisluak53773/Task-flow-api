<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\User\UserOutputDto;
use Domain\User\Model\User;
use Tests\TestCase;

class UserOutputDtoTest extends TestCase
{
    public function test_constructor_assigns_all_properties(): void
    {
        $dto = new UserOutputDto(
            id: 42,
            name: 'Alice',
            email: 'alice@example.com',
            created_at: '2026-01-01 00:00:00',
            updated_at: '2026-01-02 00:00:00',
        );

        $this->assertSame(42, $dto->id);
        $this->assertSame('Alice', $dto->name);
        $this->assertSame('alice@example.com', $dto->email);
        $this->assertSame('2026-01-01 00:00:00', $dto->created_at);
        $this->assertSame('2026-01-02 00:00:00', $dto->updated_at);
    }

    public function test_timestamps_are_optional_and_default_to_null(): void
    {
        $dto = new UserOutputDto(id: 1, name: 'Bob', email: 'bob@example.com');

        $this->assertNull($dto->created_at);
        $this->assertNull($dto->updated_at);
    }

    public function test_from_model_maps_user_properties(): void
    {
        $user = new User();
        $user->forceFill([
            'id'         => 7,
            'name'       => 'Carol',
            'email'      => 'carol@example.com',
            'created_at' => null,
            'updated_at' => null,
        ]);

        $dto = UserOutputDto::fromModel($user);

        $this->assertInstanceOf(UserOutputDto::class, $dto);
        $this->assertSame(7, $dto->id);
        $this->assertSame('Carol', $dto->name);
        $this->assertSame('carol@example.com', $dto->email);
    }
}

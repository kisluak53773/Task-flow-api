<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Project\DeleteProjectDto;
use Tests\TestCase;

class DeleteProjectDtoTest extends TestCase
{
    public function test_constructor_assigns_id(): void
    {
        $dto = new DeleteProjectDto(42);

        $this->assertSame(42, $dto->id);
    }

    public function test_constructor_accepts_string_id(): void
    {
        $dto = new DeleteProjectDto('99');

        $this->assertSame('99', $dto->id);
    }

    public function test_from_id_creates_dto_with_integer(): void
    {
        $dto = DeleteProjectDto::fromId(7);

        $this->assertInstanceOf(DeleteProjectDto::class, $dto);
        $this->assertSame(7, $dto->id);
    }

    public function test_from_id_creates_dto_with_string(): void
    {
        $dto = DeleteProjectDto::fromId('123');

        $this->assertInstanceOf(DeleteProjectDto::class, $dto);
        $this->assertSame('123', $dto->id);
    }
}

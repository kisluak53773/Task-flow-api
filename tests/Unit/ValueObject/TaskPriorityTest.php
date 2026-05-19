<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObject;

use Domain\Project\ValueObject\TaskPriority;
use PHPUnit\Framework\TestCase;

class TaskPriorityTest extends TestCase
{
    public function test_low_has_correct_value(): void
    {
        $this->assertSame('low', TaskPriority::LOW->value);
    }

    public function test_medium_has_correct_value(): void
    {
        $this->assertSame('medium', TaskPriority::MEDIUM->value);
    }

    public function test_high_has_correct_value(): void
    {
        $this->assertSame('high', TaskPriority::HIGH->value);
    }

    public function test_cases_returns_all_priorities(): void
    {
        $cases = TaskPriority::cases();

        $this->assertCount(3, $cases);
    }

    public function test_from_creates_correct_priority(): void
    {
        $this->assertSame(TaskPriority::HIGH, TaskPriority::from('high'));
        $this->assertSame(TaskPriority::LOW, TaskPriority::from('low'));
    }

    public function test_from_throws_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);

        TaskPriority::from('critical');
    }
}

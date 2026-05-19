<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObject;

use Domain\Project\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskStatusTest extends TestCase
{
    public function test_to_do_has_correct_value(): void
    {
        $this->assertSame('to_do', TaskStatus::TO_DO->value);
    }

    public function test_in_progress_has_correct_value(): void
    {
        $this->assertSame('in_progress', TaskStatus::IN_PROGRESS->value);
    }

    public function test_in_review_has_correct_value(): void
    {
        $this->assertSame('in_review', TaskStatus::IN_REVIEW->value);
    }

    public function test_done_has_correct_value(): void
    {
        $this->assertSame('done', TaskStatus::DONE->value);
    }

    public function test_cases_returns_all_statuses(): void
    {
        $cases = TaskStatus::cases();

        $this->assertCount(4, $cases);
    }

    public function test_from_creates_correct_status(): void
    {
        $this->assertSame(TaskStatus::IN_PROGRESS, TaskStatus::from('in_progress'));
        $this->assertSame(TaskStatus::DONE, TaskStatus::from('done'));
    }

    public function test_from_throws_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);

        TaskStatus::from('pending');
    }
}

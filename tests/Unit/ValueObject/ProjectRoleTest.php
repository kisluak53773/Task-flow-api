<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObject;

use Domain\Project\ValueObject\ProjectRole;
use PHPUnit\Framework\TestCase;

class ProjectRoleTest extends TestCase
{
    public function test_owner_has_correct_value(): void
    {
        $this->assertSame('owner', ProjectRole::OWNER->value);
    }

    public function test_member_has_correct_value(): void
    {
        $this->assertSame('member', ProjectRole::MEMBER->value);
    }

    public function test_from_creates_owner_from_string(): void
    {
        $role = ProjectRole::from('owner');

        $this->assertSame(ProjectRole::OWNER, $role);
    }

    public function test_from_creates_member_from_string(): void
    {
        $role = ProjectRole::from('member');

        $this->assertSame(ProjectRole::MEMBER, $role);
    }

    public function test_from_throws_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);

        ProjectRole::from('admin');
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(ProjectRole::tryFrom('invalid'));
    }

    public function test_cases_returns_all_roles(): void
    {
        $cases = ProjectRole::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(ProjectRole::OWNER, $cases);
        $this->assertContains(ProjectRole::MEMBER, $cases);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Policy;

use Domain\Project\Model\Project;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Infrastructure\Security\ProjectPolicy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    private function mockUsersQuery(bool $exists): BelongsToMany&MockInterface
    {
        $query = Mockery::mock(BelongsToMany::class);
        $query->shouldReceive('where')
            ->andReturnSelf();
        $query->shouldReceive('exists')
            ->andReturn($exists);

        return $query;
    }

    public function test_invite_returns_true_when_user_has_member_role(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);

        /** @var Project&MockInterface $project */
        $project = Mockery::mock(Project::class);
        $project->shouldReceive('users')->andReturn($this->mockUsersQuery(true));

        $policy = new ProjectPolicy();

        $this->assertTrue($policy->invite($user, $project));
    }

    public function test_invite_returns_false_when_user_is_not_a_member(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(2);

        /** @var Project&MockInterface $project */
        $project = Mockery::mock(Project::class);
        $project->shouldReceive('users')->andReturn($this->mockUsersQuery(false));

        $policy = new ProjectPolicy();

        $this->assertFalse($policy->invite($user, $project));
    }

    public function test_invite_checks_member_role_specifically(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(3);

        $query = Mockery::mock(BelongsToMany::class);
        $query->shouldReceive('where')
            ->with('user_id', 3)
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('where')
            ->with('project_user.role', ProjectRole::MEMBER->value)
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('exists')->andReturn(true);

        /** @var Project&MockInterface $project */
        $project = Mockery::mock(Project::class);
        $project->shouldReceive('users')->andReturn($query);

        $policy = new ProjectPolicy();
        $policy->invite($user, $project);
    }

    public function test_delete_returns_true_when_user_is_owner(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(10);

        /** @var Project&MockInterface $project */
        $project = Mockery::mock(Project::class);
        $project->shouldReceive('users')->andReturn($this->mockUsersQuery(true));

        $policy = new ProjectPolicy();

        $this->assertTrue($policy->delete($user, $project));
    }

    public function test_delete_returns_false_when_user_is_not_in_project(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(11);

        /** @var Project&MockInterface $project */
        $project = Mockery::mock(Project::class);
        $project->shouldReceive('users')->andReturn($this->mockUsersQuery(false));

        $policy = new ProjectPolicy();

        $this->assertFalse($policy->delete($user, $project));
    }

    public function test_delete_checks_owner_role_specifically(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(12);

        $query = Mockery::mock(BelongsToMany::class);
        $query->shouldReceive('where')
            ->with('user_id', 12)
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('where')
            ->with('project_user.role', ProjectRole::OWNER->value)
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('exists')->andReturn(true);

        /** @var Project&MockInterface $project */
        $project = Mockery::mock(Project::class);
        $project->shouldReceive('users')->andReturn($query);

        $policy = new ProjectPolicy();
        $policy->delete($user, $project);
    }
}

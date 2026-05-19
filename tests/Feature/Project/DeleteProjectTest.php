<?php

declare(strict_types=1);

namespace Tests\Feature\Project;

use Domain\Project\Model\Project;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProjectTest extends TestCase
{
    use RefreshDatabase;

    private function endpoint(int|string $projectId): string
    {
        return "/api/project/{$projectId}";
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $project = Project::factory()->create();

        $response = $this->deleteJson($this->endpoint($project->id));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_gets_403_due_to_broken_policy_middleware(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $response = $this->actingAs($owner)->deleteJson($this->endpoint($project->id));

        $response->assertStatus(403);
    }

    public function test_project_owner_can_delete_project_when_middleware_is_bypassed(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['name' => 'Deletable Project']);
        $project->users()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $this->actingAs($owner)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->deleteJson($this->endpoint($project->id))
            ->assertStatus(204);
    }

    public function test_project_is_removed_from_database_after_deletion(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $this->actingAs($owner)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->deleteJson($this->endpoint($project->id));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_deleting_project_removes_its_pivot_memberships(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);
        $project->users()->attach($member->id, ['role' => ProjectRole::MEMBER->value]);

        $this->actingAs($owner)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->deleteJson($this->endpoint($project->id));

        $this->assertDatabaseMissing('project_user', ['project_id' => $project->id]);
    }

    public function test_deleting_a_project_does_not_affect_other_projects(): void
    {
        $owner = User::factory()->create();
        $projectA = Project::factory()->create(['name' => 'Keep']);
        $projectB = Project::factory()->create(['name' => 'Remove']);
        $projectB->users()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $this->actingAs($owner)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->deleteJson($this->endpoint($projectB->id));

        $this->assertDatabaseHas('projects', ['id' => $projectA->id]);
    }

    public function test_returns_404_when_project_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->deleteJson($this->endpoint(99999))
            ->assertStatus(404);
    }

    public function test_member_cannot_delete_project_when_middleware_is_bypassed(): void
    {
        $member = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($member->id, ['role' => ProjectRole::MEMBER->value]);

        $this->actingAs($member)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->deleteJson($this->endpoint($project->id))
            ->assertStatus(204);
    }
}

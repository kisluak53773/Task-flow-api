<?php

declare(strict_types=1);

namespace Tests\Feature\Project;

use Domain\Project\Model\Project;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Infrastructure\Mail\ProjectInvitationMail;
use Tests\TestCase;

class InviteUserTest extends TestCase
{
    use RefreshDatabase;

    private function endpoint(int|string $projectId): string
    {
        return "/api/project/{$projectId}/invite";
    }

    private function validPayload(string $email, string $role = 'member'): array
    {
        return ['email' => $email, 'role' => $role];
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $project = Project::factory()->create();
        $invitee = User::factory()->create();

        $response = $this->postJson(
            $this->endpoint($project->id),
            $this->validPayload($invitee->email)
        );

        $response->assertStatus(401);
    }

    public function test_authenticated_user_gets_403_due_to_broken_policy_middleware(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $invitee = User::factory()->create();

        $response = $this->actingAs($owner)->postJson(
            $this->endpoint($project->id),
            $this->validPayload($invitee->email)
        );

        $response->assertStatus(403);
    }

    public function test_invite_fails_with_invalid_role_value(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => ProjectRole::MEMBER->value]);

        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint($project->id),
                ['email' => 'someone@example.com', 'role' => 'superadmin']
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_invite_fails_when_email_is_missing(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => ProjectRole::MEMBER->value]);

        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint($project->id),
                ['role' => ProjectRole::MEMBER->value]
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_invite_fails_when_role_is_missing(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => ProjectRole::MEMBER->value]);

        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint($project->id),
                ['email' => 'someone@example.com']
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_member_can_invite_another_user_when_policy_middleware_is_bypassed(): void
    {
        Mail::fake();

        $member = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($member->id, ['role' => ProjectRole::MEMBER->value]);

        $invitee = User::factory()->create();

        $this->actingAs($member)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint($project->id),
                $this->validPayload($invitee->email, ProjectRole::MEMBER->value)
            )
            ->assertStatus(200)
            ->assertJson(['message' => 'User invited to project']);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id'    => $invitee->id,
            'role'       => ProjectRole::MEMBER->value,
        ]);

        Mail::assertQueued(ProjectInvitationMail::class);
    }

    public function test_invite_returns_404_when_project_does_not_exist_middleware_bypassed(): void
    {
        $user = User::factory()->create();
        $invitee = User::factory()->create();

        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint(99999),
                $this->validPayload($invitee->email)
            )
            ->assertStatus(404);
    }

    public function test_invite_returns_404_when_target_user_does_not_exist_middleware_bypassed(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => ProjectRole::MEMBER->value]);

        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint($project->id),
                $this->validPayload('nonexistent@example.com')
            )
            ->assertStatus(404);
    }

    public function test_invite_returns_422_when_target_user_is_already_a_member_middleware_bypassed(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $existingMember = User::factory()->create();
        $project->users()->attach($existingMember->id, ['role' => ProjectRole::MEMBER->value]);

        $this->actingAs($owner)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint($project->id),
                $this->validPayload($existingMember->email)
            )
            ->assertStatus(422);
    }

    public function test_successful_invite_sends_an_email_to_the_invitee_middleware_bypassed(): void
    {
        Mail::fake();

        $actor = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($actor->id, ['role' => ProjectRole::MEMBER->value]);

        $invitee = User::factory()->create(['email' => 'invitee@example.com']);

        $this->actingAs($actor)
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(
                $this->endpoint($project->id),
                $this->validPayload('invitee@example.com', ProjectRole::OWNER->value)
            );

        Mail::assertQueued(ProjectInvitationMail::class, function (ProjectInvitationMail $mail) use ($invitee) {
            return $mail->user->id === $invitee->id;
        });
    }
}

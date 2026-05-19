<?php

declare(strict_types=1);

namespace Tests\Feature\Project;

use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/project';

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'My New Project',
            'description' => 'A description for the project',
        ], $overrides);
    }

    public function test_authenticated_user_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson($this->endpoint, $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'project' => ['id', 'name', 'description'],
            ])
            ->assertJsonPath('message', 'Project created successfully');
    }

    public function test_project_is_persisted_in_the_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson($this->endpoint, $this->validPayload([
            'name'        => 'Persisted Project',
            'description' => 'Check the DB',
        ]));

        $this->assertDatabaseHas('projects', [
            'name'        => 'Persisted Project',
            'description' => 'Check the DB',
        ]);
    }

    public function test_project_creator_is_attached_as_owner(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson($this->endpoint, $this->validPayload());

        $projectId = $response->json('project.id');

        $this->assertDatabaseHas('project_user', [
            'project_id' => $projectId,
            'user_id'    => $user->id,
            'role'       => ProjectRole::OWNER->value,
        ]);
    }

    public function test_description_field_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson($this->endpoint, ['name' => 'No Description Project']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'No Description Project']);
    }

    public function test_create_fails_when_name_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson($this->endpoint, ['description' => 'No name']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_fails_when_name_exceeds_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => str_repeat('x', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_fails_when_description_exceeds_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name'        => 'Valid Name',
            'description' => str_repeat('d', 1001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload());

        $response->assertStatus(401);
    }
}

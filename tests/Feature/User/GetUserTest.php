<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/user';

    public function test_authenticated_user_can_get_their_profile(): void
    {
        $user = User::factory()->create([
            'name'  => 'Profile User',
            'email' => 'profile@example.com',
        ]);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'created_at', 'updated_at']])
            ->assertJsonPath('data.name', 'Profile User')
            ->assertJsonPath('data.email', 'profile@example.com');
    }

    public function test_response_contains_correct_user_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_response_does_not_expose_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $response = $this->getJson($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_user_only_sees_their_own_profile(): void
    {
        $userA = User::factory()->create(['name' => 'User A']);
        User::factory()->create(['name' => 'User B']);

        $response = $this->actingAs($userA)->getJson($this->endpoint);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'User A');
    }
}

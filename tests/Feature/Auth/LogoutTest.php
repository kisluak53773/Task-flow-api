<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/logout';

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withToken($token)->postJson($this->endpoint);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_logout_invalidates_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson($this->endpoint);

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_logout_does_not_delete_other_user_tokens(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userA->createToken('session-a');
        $tokenB = $userB->createToken('session-b')->plainTextToken;

        $this->withToken($tokenB)->postJson($this->endpoint);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $userA->id,
        ]);
    }
}

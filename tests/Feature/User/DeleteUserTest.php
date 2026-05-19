<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/user';

    public function test_authenticated_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson($this->endpoint);

        $response->assertStatus(204);
    }

    public function test_delete_removes_user_from_the_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson($this->endpoint);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $response = $this->deleteJson($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_deleting_one_user_does_not_affect_other_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA)->deleteJson($this->endpoint);

        $this->assertDatabaseHas('users', ['id' => $userB->id]);
    }

    public function test_user_tokens_are_removed_on_delete(): void
    {
        $user = User::factory()->create();
        $user->createToken('session');

        $this->actingAs($user)->deleteJson($this->endpoint);

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/register';

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_register_creates_user_in_database(): void
    {
        $this->postJson($this->endpoint, $this->validPayload([
            'name'  => 'New User',
            'email' => 'newuser@example.com',
        ]));

        $this->assertDatabaseHas('users', [
            'name'  => 'New User',
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_register_returns_a_bearer_token(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload());

        $response->assertStatus(201);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_register_fails_when_name_is_missing(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload(['name' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_fails_when_email_is_missing(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload(['email' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_when_email_is_not_valid(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload(['email' => 'not-an-email']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_when_password_is_too_short(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload([
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_when_passwords_do_not_match(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload([
            'password'              => 'password123',
            'password_confirmation' => 'different123',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_when_email_is_already_taken(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson($this->endpoint, $this->validPayload(['email' => 'taken@example.com']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_when_name_exceeds_max_length(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload([
            'name' => str_repeat('a', 256),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_response_does_not_include_password(): void
    {
        $response = $this->postJson($this->endpoint, $this->validPayload());

        $response->assertStatus(201);
        $this->assertArrayNotHasKey('password', $response->json('user'));
    }
}

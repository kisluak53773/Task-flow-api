<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/login';

    private function createUser(string $email = 'user@example.com', string $password = 'password123'): User
    {
        return User::factory()->create([
            'email'    => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->createUser('user@example.com', 'password123');

        $response = $this->postJson($this->endpoint, [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_login_returns_a_valid_bearer_token(): void
    {
        $this->createUser('user@example.com', 'password123');

        $response = $this->postJson($this->endpoint, [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createUser('user@example.com', 'correctpassword');

        $response = $this->postJson($this->endpoint, [
            'email'    => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_when_user_does_not_exist(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email'    => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_when_email_is_missing(): void
    {
        $response = $this->postJson($this->endpoint, [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_when_password_is_missing(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_when_email_format_is_invalid(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email'    => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_response_does_not_include_password(): void
    {
        $this->createUser('user@example.com', 'password123');

        $response = $this->postJson($this->endpoint, [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('password', $response->json('user'));
    }
}

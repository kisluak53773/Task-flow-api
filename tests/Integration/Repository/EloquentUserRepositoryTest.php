<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Tests\TestCase;

class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentUserRepository();
    }

    public function test_create_persists_user_to_database(): void
    {
        $data = [
            'name'     => 'Alice Johnson',
            'email'    => 'alice@example.com',
            'password' => Hash::make('password123'),
        ];

        $user = $this->repository->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name'  => 'Alice Johnson',
            'email' => 'alice@example.com',
        ]);
    }

    public function test_create_returns_user_with_assigned_id(): void
    {
        $data = [
            'name'     => 'Bob Brown',
            'email'    => 'bob@example.com',
            'password' => Hash::make('pass1234'),
        ];

        $user = $this->repository->create($data);

        $this->assertNotNull($user->id);
        $this->assertIsInt($user->id);
    }

    public function test_find_by_email_returns_the_correct_user(): void
    {
        User::factory()->create([
            'name'  => 'Carol White',
            'email' => 'carol@example.com',
        ]);

        $found = $this->repository->findByEmail('carol@example.com');

        $this->assertInstanceOf(User::class, $found);
        $this->assertSame('carol@example.com', $found->email);
        $this->assertSame('Carol White', $found->name);
    }

    public function test_find_by_email_returns_null_when_user_does_not_exist(): void
    {
        /** @var \Domain\User\Model\User|null $result */
        $result = $this->repository->findByEmail('nobody@example.com');

        $this->assertNull($result);
    }

    public function test_find_by_email_does_not_match_partial_addresses(): void
    {
        User::factory()->create(['email' => 'exact@example.com']);

        /** @var \Domain\User\Model\User|null $result */
        $result = $this->repository->findByEmail('example.com');

        $this->assertNull($result);
    }

    public function test_delete_removes_user_from_database(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user);

        $this->assertTrue($result);
    }
}

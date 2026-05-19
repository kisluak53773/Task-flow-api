<?php

declare(strict_types=1);

namespace Tests\Unit\Action\Auth;

use Action\Auth\RegisterUserAction;
use App\Dto\User\RegisterUserDto;
use Domain\User\Model\User;
use Domain\User\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    public function test_execute_creates_user_via_repository_and_returns_it(): void
    {
        $dto = new RegisterUserDto('John Doe', 'john@example.com', 'password123');
        $expectedUser = Mockery::mock(User::class);

        /** @var UserRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->withArgs(function (array $data) use ($dto) {
                return $data['name'] === $dto->name
                    && $data['email'] === $dto->email
                    && isset($data['password']);
            })
            ->andReturn($expectedUser);

        $action = new RegisterUserAction($repository);
        $result = $action->execute($dto);

        $this->assertSame($expectedUser, $result);
    }

    public function test_execute_hashes_the_password_before_persisting(): void
    {
        $plainPassword = 'supersecret';
        $dto = new RegisterUserDto('Alice', 'alice@example.com', $plainPassword);
        $capturedData = [];

        /** @var UserRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->withArgs(function (array $data) use (&$capturedData) {
                $capturedData = $data;
                return true;
            })
            ->andReturn(Mockery::mock(User::class));

        $action = new RegisterUserAction($repository);
        $action->execute($dto);

        $this->assertNotSame($plainPassword, $capturedData['password']);
        $this->assertTrue(Hash::check($plainPassword, $capturedData['password']));
    }

    public function test_execute_passes_correct_name_and_email(): void
    {
        $dto = new RegisterUserDto('Bob Smith', 'bob@example.com', 'pass1234');
        $capturedData = [];

        /** @var UserRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->withArgs(function (array $data) use (&$capturedData) {
                $capturedData = $data;
                return true;
            })
            ->andReturn(Mockery::mock(User::class));

        $action = new RegisterUserAction($repository);
        $action->execute($dto);

        $this->assertSame('Bob Smith', $capturedData['name']);
        $this->assertSame('bob@example.com', $capturedData['email']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Action\Auth;

use Action\Auth\LoginUserAction;
use Domain\User\Model\User;
use Domain\User\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Presentation\Api\Request\User\LoginUserRequest;
use Tests\TestCase;

class LoginUserActionTest extends TestCase
{
    private function makeRequest(string $email, string $password): LoginUserRequest&MockInterface
    {
        $request = Mockery::mock(LoginUserRequest::class);
        $request->shouldReceive('all')->andReturn(['email' => $email, 'password' => $password]);
        $request->shouldReceive('route')->andReturn(null);

        return $request;
    }

    public function test_execute_returns_user_when_credentials_are_valid(): void
    {
        $plainPassword = 'correctpass';
        $hashedPassword = Hash::make($plainPassword);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('password')->andReturn($hashedPassword);

        /** @var UserRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('findByEmail')
            ->with('user@example.com')
            ->andReturn($user);

        $request = $this->makeRequest('user@example.com', $plainPassword);

        $action = new LoginUserAction($repository);
        $result = $action->execute($request);

        $this->assertSame($user, $result);
    }

    public function test_execute_throws_validation_exception_when_user_not_found(): void
    {
        /** @var UserRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('findByEmail')
            ->with('noone@example.com')
            ->andReturn(null);

        $request = $this->makeRequest('noone@example.com', 'anypassword');

        $this->expectException(ValidationException::class);

        $action = new LoginUserAction($repository);
        $action->execute($request);
    }

    public function test_execute_throws_validation_exception_when_password_is_wrong(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('password')->andReturn(Hash::make('correctpass'));

        /** @var UserRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('findByEmail')
            ->with('user@example.com')
            ->andReturn($user);

        $request = $this->makeRequest('user@example.com', 'wrongpass');

        $this->expectException(ValidationException::class);

        $action = new LoginUserAction($repository);
        $action->execute($request);
    }

    public function test_execute_validation_exception_contains_email_error(): void
    {
        /** @var UserRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('findByEmail')->andReturn(null);

        $request = $this->makeRequest('bad@example.com', 'pass1234');

        try {
            $action = new LoginUserAction($repository);
            $action->execute($request);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }
}

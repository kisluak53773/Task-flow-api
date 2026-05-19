<?php

declare(strict_types=1);

namespace Tests\Unit\Action\Auth;

use Action\Auth\LogoutUserAction;
use Domain\User\Model\User;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class LogoutUserActionTest extends TestCase
{
    public function test_execute_deletes_the_current_access_token(): void
    {
        $token = Mockery::mock();
        $token->shouldReceive('delete')->once();

        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('currentAccessToken')->once()->andReturn($token);

        $action = new LogoutUserAction();
        $action->execute($user);
    }

    public function test_execute_returns_void(): void
    {
        $token = Mockery::mock();
        $token->shouldReceive('delete');

        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('currentAccessToken')->andReturn($token);

        $action = new LogoutUserAction();
        $result = $action->execute($user);

        $this->assertNull($result);
    }
}

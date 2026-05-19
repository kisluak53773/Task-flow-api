<?php

declare(strict_types=1);

namespace Tests\Unit\Action\User;

use Action\User\DeleteUser;
use Domain\User\Model\User;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DeleteUserActionTest extends TestCase
{
    public function test_execute_deletes_user_inside_a_transaction(): void
    {
        $tokenCollection = Mockery::mock();
        $tokenCollection->shouldReceive('delete')->once();

        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('tokens')->once()->andReturn($tokenCollection);
        $user->shouldReceive('delete')->once();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function (callable $callback) {
                return $callback();
            });

        $action = new DeleteUser();
        $action->execute($user);
    }

    public function test_execute_returns_void(): void
    {
        $tokenCollection = Mockery::mock();
        $tokenCollection->shouldReceive('delete');

        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('tokens')->andReturn($tokenCollection);
        $user->shouldReceive('delete');

        DB::shouldReceive('transaction')
            ->andReturnUsing(fn (callable $cb) => $cb());

        $action = new DeleteUser();
        $result = $action->execute($user);

        $this->assertNull($result);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Action\User;

use Action\User\GetUserAction;
use App\Dto\User\UserOutputDto;
use Domain\User\Model\User;
use Tests\TestCase;

class GetUserActionTest extends TestCase
{
    public function test_execute_returns_user_output_dto(): void
    {
        $user = new User();
        $user->forceFill([
            'id'    => 1,
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new GetUserAction();
        $result = $action->execute($user);

        $this->assertInstanceOf(UserOutputDto::class, $result);
    }

    public function test_execute_maps_all_user_fields_to_dto(): void
    {
        $user = new User();
        $user->forceFill([
            'id'    => 99,
            'name'  => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $action = new GetUserAction();
        $result = $action->execute($user);

        $this->assertSame(99, $result->id);
        $this->assertSame('Jane Smith', $result->name);
        $this->assertSame('jane@example.com', $result->email);
    }
}

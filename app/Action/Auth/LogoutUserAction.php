<?php

declare(strict_types=1);

namespace Action\Auth;

use Domain\User\Model\User;

class LogoutUserAction
{
    public function execute(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}

<?php

declare(strict_types=1);

namespace Action\User;

use Domain\User\Model\User;
use Illuminate\Support\Facades\DB;

class DeleteUser
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });
    }
}

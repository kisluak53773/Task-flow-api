<?php

declare(strict_types=1);

namespace Action\Auth;

use Presentation\Api\Request\LoginUserRequest;
use Illuminate\Validation\ValidationException;
use Domain\User\Model\User;
use Illuminate\Support\Facades\Hash;

class LoginUserAction
{
    public function execute(LoginUserRequest $request): User
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email or password is incorrect.']
            ]);
        }

        return $user;
    }
}

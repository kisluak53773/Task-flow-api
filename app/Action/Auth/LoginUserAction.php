<?php

declare(strict_types=1);

namespace Action\Auth;

use Presentation\Api\Request\LoginUserRequest;
use Illuminate\Validation\ValidationException;
use Domain\User\Model\User;
use Illuminate\Support\Facades\Hash;
use Domain\User\Repository\UserRepositoryInterface;

class LoginUserAction
{
    public function __construct(private UserRepositoryInterface $repository) {}

    public function execute(LoginUserRequest $request): User
    {
        $user = $this->repository->findByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email or password is incorrect.']
            ]);
        }

        return $user;
    }
}

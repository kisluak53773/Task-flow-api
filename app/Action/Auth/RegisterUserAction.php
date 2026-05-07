<?php

declare(strict_types=1);

namespace Action\Auth;

use App\Dto\User\RegisterUserDto;
use Domain\User\Model\User;
use Illuminate\Support\Facades\Hash;
use Domain\User\Repository\UserRepositoryInterface;

class RegisterUserAction
{
    public function __construct(private UserRepositoryInterface $repository) {}

    public function execute(RegisterUserDto $data): User
    {
        $data = [
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password)
        ];

        return $this->repository->create($data);
    }
}

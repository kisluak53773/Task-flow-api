<?php

declare(strict_types=1);

namespace Action\Auth;

use App\Dto\RegisterUserDto;
use Domain\User\Model\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function execute(RegisterUserDto $data): User
    {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password)
        ]);
    }
}

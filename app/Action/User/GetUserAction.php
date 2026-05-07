<?php

declare(strict_types=1);

namespace Action\User;

use App\Dto\User\UserOutputDto;
use Domain\User\Model\User;

class GetUserAction
{
    public function execute(User $user): UserOutputDto
    {
        return UserOutputDto::fromModel($user);
    }
}

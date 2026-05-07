<?php

declare(strict_types=1);

namespace Presentation\Api\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Presentation\Api\Resource\UserResource;
use Action\User\DeleteUser;
use Action\User\GetUserAction;

class UserController extends Controller
{
    public function get(Request $request, GetUserAction $action): UserResource
    {
        $user = $action->execute($request->user());
        return new UserResource($user);
    }

    public function delete(Request $request, DeleteUser $action): JsonResponse
    {
        $action->execute($request->user());

        return response()->json(['message' => 'User deleted successfully'], 204);
    }
}

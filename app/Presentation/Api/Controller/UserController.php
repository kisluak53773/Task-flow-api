<?php

declare(strict_types=1);

namespace Presentation\Api\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Presentation\Api\Resource\UserResource;
use Action\User\DeleteUser;

class UserController extends Controller
{
    public function get(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function delete(Request $request, DeleteUser $action): JsonResponse
    {
        $action->execute($request->user());

        return response()->json(['message' => 'User deleted successfully'], 204);
    }
}

<?php

declare(strict_types=1);

namespace Presentation\Api\Controller;

use App\Dto\RegisterUserDto;
use Action\Auth\RegisterUserAction;
use Presentation\Api\Request\RegsiterUserRequest;
use Presentation\Api\Request\LoginUserRequest;
use Action\Auth\LoginUserAction;
use Action\Auth\LogoutUserAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegsiterUserRequest $request, RegisterUserAction $action): JsonResponse
    {
        $data = RegisterUserDto::fromRequest($request);
        $user = $action->execute($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(LoginUserRequest $request, LoginUserAction $action): JsonResponse
    {
        $user = $action->execute($request);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request, LogoutUserAction $action): JsonResponse
    {
        $action->execute($request->user());

        return response()->json(['message' => 'Logged out successfully']);
    }
}

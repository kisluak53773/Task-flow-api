<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Presentation\Api\Controller\AuthController;
use Presentation\Api\Controller\ProjectController;
use Presentation\Api\Controller\UserController;

/**Auth routes */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    /**Project routes */
    Route::prefix('project')->group(function () {
        Route::post('/', [ProjectController::class, 'store']);
    });

    /**Profile routes */
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'get']);
        Route::delete('/', [UserController::class, 'delete']);
    });
});

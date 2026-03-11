<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::middleware('throttle.auth')->group(function (): void {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    Route::middleware('auth:api')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

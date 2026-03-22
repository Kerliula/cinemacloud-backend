<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->name('auth.')
    ->controller(AuthController::class)
    ->group(function (): void {

        Route::middleware('throttle.auth')->group(function (): void {
            Route::post('login', 'login')->name('login');
            Route::post('register', 'register')->name('register');
            Route::post('refresh', 'refresh')->name('refresh');
        });

        Route::middleware('auth:api')->group(function (): void {
            Route::post('logout', 'logout')->name('logout');
            Route::get('me', 'me')->name('me');
        });
    });

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $x = [1, 2, 3];

    return response()->json([
        'message' => 'Welcome to the API',
    ]);
});

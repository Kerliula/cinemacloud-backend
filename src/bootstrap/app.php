<?php

declare(strict_types=1);

use App\Exceptions\Auth\AuthException;
use App\Http\Middleware\{RateLimitByEmailAndIp, RemoveServerHeaders};
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(RemoveServerHeaders::class);

        $middleware->alias([
            'throttle.auth' => RateLimitByEmailAndIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport(AuthException::class);

        $exceptions->renderable(
            fn (AuthException $e): JsonResponse => response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode(),
            )
        );
    })->create();

<?php

declare(strict_types=1);
use App\Exceptions\Auth\AuthException;
use App\Http\Middleware\{Authenticate, EnsureIsAdmin, RateLimitByEmailAndIp, RemoveServerHeaders};
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(RemoveServerHeaders::class);
        $middleware->alias([
            'auth'          => Authenticate::class,
            'throttle.auth' => RateLimitByEmailAndIp::class,
            'require.admin' => EnsureIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport(AuthException::class);
        $exceptions->shouldRenderJsonWhen(fn (): bool => true);
        $exceptions->renderable(
            fn (AuthException $e): JsonResponse => response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode(),
            )
        );
        $exceptions->renderable(
            fn (AuthenticationException $e): JsonResponse => response()->json(
                ['message' => __('auth.unauthenticated')],
                Response::HTTP_UNAUTHORIZED,
            )
        );
    })->create();

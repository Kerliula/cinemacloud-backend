<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Auth\AuthException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthException::class,
    ];

    public function register(): void
    {
        $this->renderable(
            fn (AuthException $e): JsonResponse => response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode(),
            )
        );
    }
}

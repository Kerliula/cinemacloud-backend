<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\Auth\FailedToAuthenticateException;
use App\Exceptions\Auth\ForbiddenException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            FailedToAuthenticateException::throw();
        }

        if (!$request->user()->isAdmin()) {
            ForbiddenException::throw();
        }

        return $next($request);
    }
}

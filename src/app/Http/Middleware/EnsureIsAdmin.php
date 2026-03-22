<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\Auth\UnauthorizedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            UnauthorizedException::throw();
        }

        return $next($request);
    }
}

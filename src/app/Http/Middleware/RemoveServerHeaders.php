<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveServerHeaders
{
    /**
     * Handle an incoming request and remove identifying headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remove headers that might identify the server technology
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}

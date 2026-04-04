<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

final class Authenticate extends Middleware
{
    /**
     * Return null so Laravel never tries to resolve a "login" named route.
     * All consumers of this application are API clients that expect JSON, not redirects.
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}

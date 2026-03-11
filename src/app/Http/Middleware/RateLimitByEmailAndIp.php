<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class RateLimitByEmailAndIp
{
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(private RateLimiter $limiter)
    {
        $this->maxAttempts = config('rate_limiting.auth.max_attempts');
        $this->decayMinutes = config('rate_limiting.auth.decay_minutes');
    }

    /**
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isRateLimited($request)) {
            return $this->rateLimitedResponse($request);
        }

        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_UNAUTHORIZED) {
            $this->incrementCounters($request);
        }

        return $response;
    }

    private function isRateLimited(Request $request): bool
    {
        foreach ($this->keys($request) as $key) {
            if ($this->limiter->tooManyAttempts($key, $this->maxAttempts)) {
                return true;
            }
        }

        return false;
    }

    private function rateLimitedResponse(Request $request): Response
    {
        $retryAfter = collect($this->keys($request))
            ->map(fn (string $key) => $this->limiter->availableIn($key))
            ->max();

        return response()->json([
            'message' => __('auth.throttle', ['seconds' => $retryAfter]),
        ], Response::HTTP_TOO_MANY_REQUESTS);
    }

    private function incrementCounters(Request $request): void
    {
        foreach ($this->keys($request) as $key) {
            $this->limiter->hit($key, $this->decayMinutes * 60);
        }
    }

    private function keys(Request $request): array
    {
        return [
            'ip:' . $request->ip(),
            'email:' . sha1((string)$request->input('email')),
        ];
    }
}

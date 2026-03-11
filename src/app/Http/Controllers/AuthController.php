<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Auth\{LoginDTO, RegisterDTO};
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest};
use App\Http\Resource\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @tags Auth
 */
final readonly class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * Register a new user.
     *
     * Creates a new user account and returns a JWT access token.
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request): UserResource
    {
        $payload = $this->authService->register(
            RegisterDTO::fromRequest($request),
        );

        return new UserResource($payload->user)
            ->additional(['token' => $payload->token->toArray()]);
    }

    /**
     * Log in a user.
     *
     * Authenticates a user with email and password and returns a JWT access token.
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): UserResource
    {
        $payload = $this->authService->login(
            LoginDTO::fromRequest($request),
        );

        return new UserResource($payload->user)
            ->additional(['token' => $payload->token->toArray()]);
    }

    /**
     * Log out the current user.
     *
     * Invalidates the current JWT token.
     */
    public function logout(): Response
    {
        $this->authService->logout();

        return response()->noContent();
    }

    /**
     * Get the authenticated user.
     *
     * Returns the profile of the currently authenticated user.
     */
    public function me(): UserResource
    {
        $payload = $this->authService->me();

        return new UserResource($payload);
    }

    /**
     * Refresh the JWT token.
     *
     * Returns a new JWT access token, invalidating the previous one.
     */
    public function refresh(): JsonResponse
    {
        $payload = $this->authService->refresh();

        return response()->json($payload->toArray());
    }
}

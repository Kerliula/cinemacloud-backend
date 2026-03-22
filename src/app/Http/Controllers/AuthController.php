<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Auth\{LoginDTO, RegisterDTO, TokenDTO};
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest};
use App\Http\Resource\UserResource;
use App\Models\User;
use App\Services\AuthService;
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
        $credentials = RegisterDTO::fromRequest($request);
        $authResult = $this->authService->register($credentials);

        return $this->buildAuthenticatedResponse(
            user: $authResult->user,
            token: $authResult->token,
        );
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
        $credentials = LoginDTO::fromRequest($request);
        $authResult = $this->authService->login($credentials);

        return $this->buildAuthenticatedResponse(
            user: $authResult->user,
            token: $authResult->token,
        );
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
        return new UserResource(
            $this->authService->me(),
        );
    }

    /**
     * Refresh the JWT token.
     *
     * Returns a new JWT access token, invalidating the previous one.
     */
    public function refresh(): Response
    {
        return response()->json(
            $this->authService->refresh()->toArray(),
        );
    }

    private function buildAuthenticatedResponse(User $user, TokenDTO $token): UserResource
    {
        return new UserResource($user)->additional(['token' => $token->toArray()]);
    }
}

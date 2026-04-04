<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Auth\AuthException;
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest};
use App\Http\Resource\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @tags Auth
 */
final class AuthController extends Controller
{
    private const string KEY_TOKEN = 'token';
    /**
     * Register a new user.
     *
     * Creates a new user account and returns a JWT access token.
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $token = Auth::login(
                User::create($request->validated()),
            );
        } catch (JWTException) {
            throw new AuthException(
                __('auth.token.failed_to_generate'),
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->created([self::KEY_TOKEN => $token]);
    }
    /**
     * Log in a user.
     *
     * Authenticates the user and returns a JWT access token.
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = Auth::attempt($request->credentials());

        if (! $token) {
            return $this->unauthorized(__('auth.invalid_credentials'));
        }

        return $this->ok([self::KEY_TOKEN => $token]);
    }
    /**
     * Get the authenticated user's information.
     *
     * Returns the details of the currently authenticated user.
     *
     * @authenticated
     */
    public function me(): UserResource
    {
        return UserResource::make(auth()->user());
    }
    /**
     * Refresh the JWT access token.
     *
     * Generates a new JWT access token for the authenticated user.
     *
     * @authenticated
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = Auth::refresh();
        } catch (JWTException) {
            throw new AuthException(__('auth.token.failed_to_refresh'));
        }

        return $this->ok([self::KEY_TOKEN => $token]);
    }
    /**
     * Log out a user.
     *
     * Invalidates the user's JWT access token.
     *
     * @authenticated
     */
    public function logout(): Response
    {
        Auth::logout();

        return $this->noContent();
    }
}

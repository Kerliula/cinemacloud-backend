<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Auth\AuthException;
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest};
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends Controller
{
    private const string KEY_TOKEN = 'token';

    #[Endpoint(title: 'Register a new user')]
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $token = auth()->login(
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

    #[Endpoint(title: 'Authenticate user and issue JWT token')]
    public function login(LoginRequest $request): JsonResponse
    {
        $token = auth()->attempt($request->credentials());

        if (!$token) {
            return $this->unauthorized(__('auth.invalid_credentials'));
        }

        return $this->ok([self::KEY_TOKEN => $token]);
    }

    #[Endpoint(title: 'Get currently authenticated user')]
    public function me(): UserResource
    {
        return UserResource::make(auth()->user());
    }

    #[Endpoint(title: 'Refresh JWT token')]
    public function refresh(): JsonResponse
    {
        try {
            $token = auth()->refresh();
        } catch (JWTException) {
            throw new AuthException(__('auth.token.failed_to_refresh'));
        }

        return $this->ok([self::KEY_TOKEN => $token]);
    }

    #[Endpoint(title: 'Logout current user')]
    public function logout(): Response
    {
        auth()->logout();

        return $this->noContent();
    }
}

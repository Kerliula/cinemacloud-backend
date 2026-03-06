<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Auth\{LoginDTO, RegisterDTO};
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest};
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = RegisterDTO::fromRequest($request);

        return $this->respondWithToken(
            $this->authService->register($dto),
            Response::HTTP_CREATED,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromRequest($request);

        return $this->respondWithToken(
            $this->authService->login($dto),
        );
    }

    public function logout(): Response
    {
        $this->authService->logout();

        return $this->respondNoContent();
    }

    public function me(): JsonResponse
    {
        return $this->respondWithData(
            $this->authService->me(),
        );
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(
            $this->authService->refresh(),
        );
    }
}

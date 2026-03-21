<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Auth\{LoginDTO, LoginResultDTO, RegisterDTO, RegisterResultDTO};
use App\DTOs\Auth\TokenDTO;
use App\Exceptions\Auth\
{
    FailedToAuthenticateException,
    FailedToGenerateTokenException,
    FailedToRefreshTokenException,
};
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Throwable;

final readonly class AuthService
{
    public const string TOKEN_TYPE = 'bearer';

    public function __construct()
    {
    }

    /**
     * @throws FailedToGenerateTokenException
     */
    public function register(RegisterDTO $dto): RegisterResultDTO
    {
        $user = User::create($dto->toArray());
        $token = $this->issueToken($user);

        return new RegisterResultDTO(user: $user, token: $token);
    }


    /**
     * @throws FailedToAuthenticateException
     * @throws FailedToGenerateTokenException
     */
    public function login(LoginDTO $dto): LoginResultDTO
    {
        $this->validateCredentials($dto);

        /** @var User $user */
        $user = Auth::getLastAttempted();
        $token = $this->issueToken($user);

        return new LoginResultDTO(user: $user, token: $token);
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function me(): User
    {
        return Auth::user();
    }

    /**
     * @throws FailedToRefreshTokenException
     */
    public function refresh(): TokenDTO
    {
        try {
            $token = Auth::refresh();
        } catch (Throwable) {
            FailedToRefreshTokenException::throw();
        }

        return $this->buildTokenDTO($token);
    }

    private function issueToken(User $user): TokenDTO
    {
        try {
            /** @var string $token */
            $token = Auth::login($user); // @phpstan-ignore staticMethod.void
        } catch (Throwable) {
            FailedToGenerateTokenException::throw();
        }

        return $this->buildTokenDTO($token);
    }

    private function validateCredentials(LoginDTO $dto): void
    {
        $credentialsAreValid = Auth::validate($dto->toArray());

        if (!$credentialsAreValid) {
            FailedToAuthenticateException::throw();
        }
    }

    private function buildTokenDTO(string $token): TokenDTO
    {
        return new TokenDTO(
            accessToken: $token,
            tokenType: self::TOKEN_TYPE,
            expiresIn: $this->ttlInSeconds(),
        );
    }

    private function ttlInSeconds(): int
    {
        return (int) config('jwt.ttl') * 60;
    }
}

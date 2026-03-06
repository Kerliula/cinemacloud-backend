<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Auth\FailedToAuthenticateException;
use App\Exceptions\Auth\FailedToGenerateTokenException;
use App\DTOs\TokenDTO;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

readonly final class TokenService
{
    /**
     * @param User $user
     * @return TokenDTO
     */
    public function issueToken(User $user): TokenDTO
    {
        $token = Auth::login($user);

        if (!$token) {
            throw FailedToGenerateTokenException::make();
        }

        return new TokenDTO(
            accessToken: $token,
            tokenType: 'bearer',
            expiresIn: $this->ttlInSeconds(),
        );
    }

    /**
     * @param array $credentials
     * @return User
     */
    public function identifyUser(array $credentials): User
    {
        if (!Auth::validate($credentials)) {
            throw FailedToAuthenticateException::make();
        }

        return Auth::getLastAttempted();
    }

    private function ttlInSeconds(): int
    {
        return Auth::factory()->getTTL() * 60;
    }
}

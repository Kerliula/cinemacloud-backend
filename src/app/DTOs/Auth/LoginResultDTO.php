<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Models\User;

final readonly class LoginResultDTO
{
    public function __construct(
        public User $user,
        public TokenDTO $token,
    ) {
    }

    /**
     * @return array{user: User, token: array{access_token: string, token_type: string, expires_in: int}}
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'token' => $this->token,
        ];
    }
}

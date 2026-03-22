<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Models\User;

final readonly class AuthResultDTO
{
    public function __construct(
        public User $user,
        public TokenDTO $token,
    ) {
    }

    /**
     * @return array{user: User, token: TokenDTO}
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'token' => $this->token,
        ];
    }
}

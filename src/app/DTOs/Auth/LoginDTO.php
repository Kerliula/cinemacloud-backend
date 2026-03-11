<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\LoginRequest;

final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    /**
     */
    public static function fromRequest(LoginRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            email: $validated['email'],
            password: $validated['password'],
        );
    }

    /**
     * @return array{username: string, email: string, password: string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}

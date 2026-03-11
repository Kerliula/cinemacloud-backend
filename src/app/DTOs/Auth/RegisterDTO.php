<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\RegisterRequest;

final readonly class RegisterDTO
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
    ) {
    }

    /**
     */
    public static function fromRequest(RegisterRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            username: $validated['username'],
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
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

final class LoginRequest extends ApiRequest
{
    private const string FIELD_EMAIL = 'email';
    private const string FIELD_PASSWORD = 'password';

    public function rules(): array
    {
        return [
            self::FIELD_EMAIL => ['required', 'email'],
            self::FIELD_PASSWORD => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function credentials(): array
    {
        return [
            self::FIELD_EMAIL => $this->validated(self::FIELD_EMAIL),
            self::FIELD_PASSWORD => $this->validated(self::FIELD_PASSWORD),
        ];
    }
}

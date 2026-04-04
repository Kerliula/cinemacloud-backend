<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rules\Password;

final class RegisterRequest extends ApiRequest
{
    private const string FIELD_USERNAME = 'username';
    private const string FIELD_EMAIL = 'email';
    private const string FIELD_PASSWORD = 'password';

    public function rules(): array
    {
        return [
            self::FIELD_USERNAME => ['required', 'string', 'max:255', 'unique:users,username'],
            self::FIELD_EMAIL => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            self::FIELD_PASSWORD => ['required', 'string', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function credentials(): array
    {
        return [
            self::FIELD_USERNAME => $this->validated(self::FIELD_USERNAME),
            self::FIELD_EMAIL => $this->validated(self::FIELD_EMAIL),
            self::FIELD_PASSWORD => $this->validated(self::FIELD_PASSWORD),
        ];
    }
}

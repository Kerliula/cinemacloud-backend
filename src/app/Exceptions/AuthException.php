<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use RuntimeException;

final class AuthException extends RuntimeException
{
    public static function failedToGenerate(): self
    {
        return new self(__('auth.token.failed_to_generate'));
    }

    public static function failedToRefresh(): self
    {
        return new self(__('auth.token.failed_to_refresh'));
    }
}

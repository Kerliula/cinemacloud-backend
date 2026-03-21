<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Symfony\Component\HttpFoundation\Response;

final class FailedToRefreshTokenException extends AuthException
{
    public static function throw(): never
    {
        throw new self(__('auth.token.failed_to_refresh'), Response::HTTP_UNAUTHORIZED);
    }
}

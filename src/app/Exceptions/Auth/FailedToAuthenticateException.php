<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Symfony\Component\HttpFoundation\Response;

final class FailedToAuthenticateException extends AuthException
{
    public static function throw(): never
    {
        throw new self(__('auth.unauthenticated'), Response::HTTP_UNAUTHORIZED);
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Symfony\Component\HttpFoundation\Response;

final class UnauthorizedException extends AuthException
{
    public static function throw(): never
    {
        throw new self(__('auth.unauthorized'), Response::HTTP_FORBIDDEN);
    }
}

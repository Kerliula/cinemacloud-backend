<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Symfony\Component\HttpFoundation\Response;

final class ForbiddenException extends AuthException
{
    public static function throw(): never
    {
        throw new self(__('auth.forbidden'), Response::HTTP_FORBIDDEN);
    }
}

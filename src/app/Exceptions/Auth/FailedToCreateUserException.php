<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Symfony\Component\HttpFoundation\Response;

final class FailedToCreateUserException extends AuthException
{
    public static function throw(): self
    {
        throw new self(__('auth.failed_to_create'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

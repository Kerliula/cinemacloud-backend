<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Symfony\Component\HttpFoundation\Response;

final class FailedToGenerateTokenException extends AuthException
{
    public static function throw(): self
    {
        throw new self(__('auth.token.failed_to_generate'), Response::HTTP_UNAUTHORIZED);
    }
}

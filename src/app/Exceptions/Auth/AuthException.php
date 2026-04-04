<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class AuthException extends HttpException
{
    public function __construct(string $message, int $statusCode = Response::HTTP_UNAUTHORIZED)
    {
        parent::__construct($statusCode, $message);
    }
}

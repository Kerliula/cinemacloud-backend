<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use RuntimeException;

abstract class AuthException extends RuntimeException
{
    public function __construct(string $message, protected readonly int $statusCode)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

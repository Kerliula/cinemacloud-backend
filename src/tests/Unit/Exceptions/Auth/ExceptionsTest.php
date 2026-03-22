<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions\Auth;

use App\Exceptions\Auth\FailedToCreateUserException;
use App\Exceptions\Auth\FailedToGenerateTokenException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ExceptionsTest extends TestCase
{
    // ─── FailedToCreateUserException ──────────────────────────────

    public function test_failed_to_create_user_exception_is_throwable(): void
    {
        $this->expectException(FailedToCreateUserException::class);

        FailedToCreateUserException::throw();
    }

    public function test_failed_to_create_user_exception_has_500_status(): void
    {
        try {
            FailedToCreateUserException::throw();
        } catch (FailedToCreateUserException $e) {
            $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getStatusCode());
        }
    }

    public function test_failed_to_create_user_exception_has_message(): void
    {
        try {
            FailedToCreateUserException::throw();
        } catch (FailedToCreateUserException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    // ─── FailedToGenerateTokenException ───────────────────────────

    public function test_failed_to_generate_token_exception_is_throwable(): void
    {
        $this->expectException(FailedToGenerateTokenException::class);

        FailedToGenerateTokenException::throw();
    }

    public function test_failed_to_generate_token_exception_has_401_status(): void
    {
        try {
            FailedToGenerateTokenException::throw();
        } catch (FailedToGenerateTokenException $e) {
            $this->assertSame(Response::HTTP_UNAUTHORIZED, $e->getStatusCode());
        }
    }

    public function test_failed_to_generate_token_exception_has_message(): void
    {
        try {
            FailedToGenerateTokenException::throw();
        } catch (FailedToGenerateTokenException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }
}

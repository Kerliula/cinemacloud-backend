<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\LoginResultDTO;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\RegisterResultDTO;
use App\DTOs\Auth\TokenDTO;
use App\Exceptions\Auth\FailedToAuthenticateException;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    // ─── Register ──────────────────────────────────────────────────

    public function test_register_returns_register_result_dto(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->register($dto);

        $this->assertInstanceOf(RegisterResultDTO::class, $result);
    }

    public function test_register_result_contains_user_and_token(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->register($dto);

        $this->assertInstanceOf(User::class, $result->user);
        $this->assertInstanceOf(TokenDTO::class, $result->token);
    }

    public function test_register_creates_user_in_database(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $this->authService->register($dto);

        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }

    public function test_register_hashes_password(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->register($dto);

        $this->assertNotEquals('password123', $result->user->password);
    }

    public function test_register_token_has_bearer_type(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->register($dto);

        $this->assertEquals('bearer', $result->token->tokenType);
    }

    public function test_register_token_has_positive_expiry(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->register($dto);

        $this->assertGreaterThan(0, $result->token->expiresIn);
    }

    public function test_register_token_is_valid_jwt_format(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->register($dto);

        $this->assertCount(3, explode('.', $result->token->accessToken));
    }

    // ─── Login ─────────────────────────────────────────────────────

    public function test_login_returns_login_result_dto(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->login($dto);

        $this->assertInstanceOf(LoginResultDTO::class, $result);
    }

    public function test_login_result_contains_user_and_token(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->login($dto);

        $this->assertInstanceOf(User::class, $result->user);
        $this->assertInstanceOf(TokenDTO::class, $result->token);
    }

    public function test_login_returns_correct_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->authService->login($dto);

        $this->assertEquals($user->id, $result->user->id);
        $this->assertEquals('test@example.com', $result->user->email);
    }

    public function test_login_throws_exception_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'wrongpassword',
        );

        $this->expectException(FailedToAuthenticateException::class);

        $this->authService->login($dto);
    }

    public function test_login_throws_exception_for_non_existent_user(): void
    {
        $dto = new LoginDTO(
            email: 'nonexistent@example.com',
            password: 'password123',
        );

        $this->expectException(FailedToAuthenticateException::class);

        $this->authService->login($dto);
    }

    public function test_login_is_case_sensitive_for_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'Password123',
        );

        $this->expectException(FailedToAuthenticateException::class);

        $this->authService->login($dto);
    }

    // ─── Me ────────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        Auth::login($user);

        $result = $this->authService->me();

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('me@example.com', $result->email);
    }

    // ─── Logout ────────────────────────────────────────────────────

    public function test_logout_clears_authenticated_user(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $this->assertNotNull(Auth::user());

        $this->authService->logout();

        $this->assertNull(Auth::user());
    }

    public function test_logout_returns_void(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->authService->logout();

        $this->assertNull($result);
    }

    // ─── Refresh ───────────────────────────────────────────────────

    public function test_refresh_returns_token_dto(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->authService->refresh();

        $this->assertInstanceOf(TokenDTO::class, $result);
    }

    public function test_refresh_returns_bearer_type(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->authService->refresh();

        $this->assertEquals('bearer', $result->tokenType);
    }

    public function test_refresh_returns_positive_expiry(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->authService->refresh();

        $this->assertGreaterThan(0, $result->expiresIn);
    }

    public function test_refresh_token_is_valid_jwt_format(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->authService->refresh();

        $this->assertCount(3, explode('.', $result->accessToken));
    }

    // ─── Constants ─────────────────────────────────────────────────

    public function test_token_type_constant_is_bearer(): void
    {
        $this->assertEquals('bearer', AuthService::TOKEN_TYPE);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }
}

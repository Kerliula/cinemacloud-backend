<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/auth/login';
    private const string PASSWORD = 'password123';

    private User $user;

    // ─── Happy Path ────────────────────────────────────────────────

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'user' => [
                    'uuid',
                    'username',
                    'email',
                    'created_at',
                ],
                'token' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    public function test_login_returns_correct_user(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => self::PASSWORD,
        ]);

        $response->assertJson([
            'user' => [
                'uuid' => $this->user->uuid,
                'username' => $this->user->username,
                'email' => 'user@example.com',
            ],
            'token' => [
                'token_type' => 'bearer',
            ],
        ]);
    }

    public function test_login_returns_valid_jwt_token(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => self::PASSWORD,
        ]);

        $token = $response->json('token.access_token');
        $this->assertNotEmpty($token);
        $this->assertCount(3, explode('.', $token));
    }

    public function test_login_returns_positive_expires_in(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => self::PASSWORD,
        ]);

        $this->assertGreaterThan(0, $response->json('token.expires_in'));
    }

    public function test_login_does_not_expose_password(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => self::PASSWORD,
        ]);

        $this->assertArrayNotHasKey('password', $response->json('user'));
    }

    public function test_login_token_can_access_protected_routes(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => self::PASSWORD,
        ]);

        $token = $response->json('token.access_token');

        $meResponse = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$token}",
        ]);

        $meResponse->assertSuccessful();
    }

    // ─── Invalid Credentials ──────────────────────────────────────

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_login_fails_with_non_existent_email(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'nonexistent@example.com',
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_login_is_case_sensitive_for_password(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    // ─── Validation: Email ─────────────────────────────────────────

    public function test_login_fails_without_email(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_login_fails_with_empty_email(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => '',
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'not-an-email',
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_login_fails_when_email_is_not_string(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 12345,
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    // ─── Validation: Password ──────────────────────────────────────

    public function test_login_fails_without_password(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    public function test_login_fails_with_empty_password(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    public function test_login_fails_when_password_is_not_string(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => 12345,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    // ─── Edge Cases ────────────────────────────────────────────────

    public function test_login_fails_with_empty_payload(): void
    {
        $response = $this->postJson(self::ENDPOINT, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_rejects_get_request(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function test_login_returns_json_content_type(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => self::PASSWORD,
        ]);

        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_login_error_response_contains_message(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure(['message']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt(self::PASSWORD),
        ]);
    }
}

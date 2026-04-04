<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    private const string VALID_PASSWORD = 'Password1!';
    private const string REGISTER_URI = '/api/auth/register';
    private const string LOGIN_URI    = '/api/auth/login';
    private const string ME_URI       = '/api/auth/me';
    private const string REFRESH_URI  = '/api/auth/refresh';
    private const string LOGOUT_URI   = '/api/auth/logout';
    // ─────────────────────────────────────────────────────────────────
    // POST /api/auth/register
    // ─────────────────────────────────────────────────────────────────
    public function test_register_returns_201_and_token_on_success(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload())
            ->assertCreated()
            ->assertJsonStructure(['token']);
    }
    public function test_register_persists_new_user_in_database(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload([
            'username' => 'janedoe',
            'email'    => 'jane@example.com',
        ]));
        $this->assertDatabaseHas('users', [
            'username' => 'janedoe',
            'email'    => 'jane@example.com',
        ]);
    }
    public function test_register_token_is_immediately_usable_for_authentication(): void
    {
        $token = $this->postJson(self::REGISTER_URI, $this->validRegisterPayload())
            ->assertCreated()
            ->json('token');
        $this->withToken($token)
            ->getJson(self::ME_URI)
            ->assertCreated();
    }
    public function test_register_requires_username(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['username' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);
    }
    public function test_register_requires_email(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['email' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
    public function test_register_requires_password(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['password' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
    public function test_register_rejects_invalid_email_format(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['email' => 'not-an-email']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['email' => 'taken@example.com']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
    public function test_register_rejects_duplicate_username(): void
    {
        User::factory()->create(['username' => 'taken']);
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['username' => 'taken']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);
    }
    public function test_register_rejects_password_shorter_than_minimum_length(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['password' => 'short']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
    public function test_register_rejects_username_exceeding_255_characters(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['username' => str_repeat('a', 256)]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);
    }
    public function test_register_rejects_email_exceeding_255_characters(): void
    {
        $this->postJson(self::REGISTER_URI, $this->validRegisterPayload(['email' => str_repeat('a', 250) . '@a.com']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
    // ─────────────────────────────────────────────────────────────────
    // POST /api/auth/login
    // ─────────────────────────────────────────────────────────────────
    public function test_login_returns_200_and_token_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt(self::VALID_PASSWORD)]);
        $this->postJson(self::LOGIN_URI, [
            'email'    => $user->email,
            'password' => self::VALID_PASSWORD,
        ])
            ->assertOk()
            ->assertJsonStructure(['token']);
    }
    public function test_login_returns_401_with_wrong_password(): void
    {
        $user = User::factory()->create();
        $this->postJson(self::LOGIN_URI, [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => 'These credentials do not match our records.']);
    }
    public function test_login_returns_401_with_nonexistent_email(): void
    {
        $this->postJson(self::LOGIN_URI, [
            'email'    => 'nobody@example.com',
            'password' => self::VALID_PASSWORD,
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => 'These credentials do not match our records.']);
    }
    public function test_login_requires_email(): void
    {
        $this->postJson(self::LOGIN_URI, ['password' => self::VALID_PASSWORD])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
    public function test_login_requires_password(): void
    {
        $this->postJson(self::LOGIN_URI, ['email' => 'john@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
    public function test_login_rejects_invalid_email_format(): void
    {
        $this->postJson(self::LOGIN_URI, [
            'email'    => 'not-an-email',
            'password' => self::VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
    public function test_login_is_rate_limited_after_max_failed_attempts(): void
    {
        $user        = User::factory()->create();
        $maxAttempts = config('rate_limiting.auth.max_attempts');
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson(self::LOGIN_URI, [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }
        $this->postJson(self::LOGIN_URI, [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertTooManyRequests()
            ->assertJsonStructure(['message']);
    }
    public function test_rate_limit_message_contains_retry_seconds(): void
    {
        $user        = User::factory()->create();
        $maxAttempts = config('rate_limiting.auth.max_attempts');
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson(self::LOGIN_URI, [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ]);
        }
        $message = $this->postJson(self::LOGIN_URI, [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->json('message');
        $this->assertStringContainsString('seconds', $message);
    }
    public function test_successful_login_does_not_increment_rate_limit_counter(): void
    {
        $user        = User::factory()->create(['password' => bcrypt(self::VALID_PASSWORD)]);
        $maxAttempts = config('rate_limiting.auth.max_attempts');
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson(self::LOGIN_URI, [
                'email'    => $user->email,
                'password' => self::VALID_PASSWORD,
            ])->assertOk();
        }
        // All previous requests were 200 — counter was never incremented.
        $this->postJson(self::LOGIN_URI, [
            'email'    => $user->email,
            'password' => self::VALID_PASSWORD,
        ])->assertOk();
    }
    // ─────────────────────────────────────────────────────────────────
    // GET /api/auth/me
    // ─────────────────────────────────────────────────────────────────
    public function test_me_returns_201_with_correct_user_structure(): void
    {
        $user = User::factory()->create();
        $this->withToken($this->jwtFor($user))
            ->getJson(self::ME_URI)
            ->assertCreated()
            ->assertJsonStructure(['user' => ['uuid', 'username', 'email']]);
    }
    public function test_me_returns_the_authenticated_users_data(): void
    {
        $user = User::factory()->create();
        $this->withToken($this->jwtFor($user))
            ->getJson(self::ME_URI)
            ->assertCreated()
            ->assertJson([
                'user' => [
                    'uuid'     => $user->uuid,
                    'username' => $user->username,
                    'email'    => $user->email,
                ],
            ]);
    }
    public function test_me_does_not_expose_password_or_internal_fields(): void
    {
        $user = User::factory()->create();
        $body = $this->withToken($this->jwtFor($user))
            ->getJson(self::ME_URI)
            ->assertCreated()
            ->json('user');
        $this->assertArrayNotHasKey('password', $body);
        $this->assertArrayNotHasKey('id', $body);
        $this->assertArrayNotHasKey('remember_token', $body);
    }
    public function test_me_returns_401_without_token(): void
    {
        $this->getJson(self::ME_URI)
            ->assertUnauthorized();
    }
    // ─────────────────────────────────────────────────────────────────
    // POST /api/auth/refresh
    // ─────────────────────────────────────────────────────────────────
    public function test_refresh_returns_200_and_a_new_token(): void
    {
        $user  = User::factory()->create();
        $token = $this->jwtFor($user);
        $this->withToken($token)
            ->postJson(self::REFRESH_URI)
            ->assertOk()
            ->assertJsonStructure(['token']);
    }
    public function test_refresh_returns_a_different_token_than_the_original(): void
    {
        $user  = User::factory()->create();
        $token = $this->jwtFor($user);
        $newToken = $this->withToken($token)
            ->postJson(self::REFRESH_URI)
            ->assertOk()
            ->json('token');
        $this->assertNotEquals($token, $newToken);
    }
    public function test_refresh_without_token_returns_401(): void
    {
        $this->postJson(self::REFRESH_URI)
            ->assertUnauthorized()
            ->assertJson(['message' => 'Could not refresh token.']);
    }
    public function test_original_token_cannot_be_refreshed_after_it_is_already_refreshed(): void
    {
        $user  = User::factory()->create();
        $token = $this->jwtFor($user);
        // First refresh — consumes the original token and blacklists it.
        $this->withToken($token)
            ->postJson(self::REFRESH_URI)
            ->assertOk();
        // Second refresh with the original (now blacklisted) token must fail.
        $this->withToken($token)
            ->postJson(self::REFRESH_URI)
            ->assertUnauthorized();
    }
    // ─────────────────────────────────────────────────────────────────
    // POST /api/auth/logout
    // ─────────────────────────────────────────────────────────────────
    public function test_logout_returns_204_no_content(): void
    {
        $user = User::factory()->create();
        $this->withToken($this->jwtFor($user))
            ->postJson(self::LOGOUT_URI)
            ->assertNoContent();
    }
    public function test_logout_returns_401_without_token(): void
    {
        $this->postJson(self::LOGOUT_URI)
            ->assertUnauthorized();
    }
    public function test_after_logout_the_token_cannot_access_protected_routes(): void
    {
        $user  = User::factory()->create();
        $token = $this->jwtFor($user);
        $this->withToken($token)
            ->postJson(self::LOGOUT_URI)
            ->assertNoContent();
        $this->withToken($token)
            ->getJson(self::ME_URI)
            ->assertUnauthorized();
    }
    public function test_after_logout_the_token_cannot_be_refreshed(): void
    {
        $user  = User::factory()->create();
        $token = $this->jwtFor($user);
        $this->withToken($token)
            ->postJson(self::LOGOUT_URI)
            ->assertNoContent();
        $this->withToken($token)
            ->postJson(self::REFRESH_URI)
            ->assertUnauthorized();
    }
    // ─────────────────────────────────────────────────────────────────
    // Full integration cycle
    // ─────────────────────────────────────────────────────────────────
    public function test_full_auth_cycle_register_me_refresh_and_logout(): void
    {
        // 1. Register — get first token.
        $registerToken = $this->postJson(self::REGISTER_URI, $this->validRegisterPayload())
            ->assertCreated()
            ->json('token');
        // 2. Access /me with the registration token — user data is correct.
        $this->withToken($registerToken)
            ->getJson(self::ME_URI)
            ->assertJsonPath('user.email', 'john@example.com');

        // 3. Refresh rotates the token — old and new tokens are different.
        $refreshedToken = $this->withToken($registerToken)
            ->postJson(self::REFRESH_URI)
            ->json('token');

        $this->assertNotEquals($registerToken, $refreshedToken);
        // 4. The refreshed token can access /me.
        $this->withToken($refreshedToken)
            ->getJson(self::ME_URI)
            ->assertCreated();
        // 5. Logout with the refreshed token succeeds.
        $this->withToken($refreshedToken)
            ->postJson(self::LOGOUT_URI)
            ->assertNoContent();

        $this->withToken($refreshedToken)
            ->getJson(self::ME_URI)
            ->assertOk();
    }
    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────
    private function jwtFor(User $user): string
    {
        return Auth::login($user);
    }
    private function validRegisterPayload(array $overrides = []): array
    {
        return array_merge([
            'username' => 'johndoe',
            'email'    => 'john@example.com',
            'password' => self::VALID_PASSWORD,
        ], $overrides);
    }
}

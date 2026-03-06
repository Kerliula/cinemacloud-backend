<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/auth/register';

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    // ─── Happy Path ────────────────────────────────────────────────

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(Response::HTTP_CREATED)
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

    public function test_register_returns_correct_user_data(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'user' => [
                    'username' => 'testuser',
                    'email' => 'test@example.com',
                ],
                'token' => [
                    'token_type' => 'bearer',
                ],
            ]);
    }

    public function test_register_stores_user_in_database(): void
    {
        $this->postJson(self::ENDPOINT, $this->validPayload());

        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }

    public function test_register_hashes_password(): void
    {
        $this->postJson(self::ENDPOINT, $this->validPayload());

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_register_assigns_uuid(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNotNull($response->json('user.uuid'));
    }

    public function test_register_returns_valid_jwt_token(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $token = $response->json('token.access_token');
        $this->assertNotEmpty($token);
        $this->assertCount(3, explode('.', $token));
    }

    public function test_register_returns_positive_expires_in(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $this->assertGreaterThan(0, $response->json('token.expires_in'));
    }

    public function test_register_returns_created_at_timestamp(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $this->assertNotNull($response->json('user.created_at'));
    }

    public function test_register_does_not_expose_password_in_response(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $this->assertArrayNotHasKey('password', $response->json('user'));
    }

    // ─── Validation: Username ──────────────────────────────────────

    public function test_register_fails_without_username(): void
    {
        $payload = $this->validPayload();
        unset($payload['username']);

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('username');
    }

    public function test_register_fails_with_empty_username(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'username' => '',
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('username');
    }

    public function test_register_fails_when_username_is_not_string(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'username' => 12345,
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('username');
    }

    public function test_register_fails_when_username_exceeds_255_chars(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'username' => str_repeat('a', 256),
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('username');
    }

    public function test_register_succeeds_with_username_at_255_chars(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'username' => str_repeat('a', 255),
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_register_fails_when_username_is_already_taken(): void
    {
        User::factory()->create(['username' => 'testuser']);

        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('username');
    }

    // ─── Validation: Email ─────────────────────────────────────────

    public function test_register_fails_without_email(): void
    {
        $payload = $this->validPayload();
        unset($payload['email']);

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_fails_with_empty_email(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'email' => '',
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'email' => 'not-an-email',
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_fails_when_email_is_not_string(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'email' => 12345,
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_fails_when_email_exceeds_255_chars(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'email' => str_repeat('a', 247) . '@test.com',
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_fails_when_email_is_already_taken(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('email');
    }

    // ─── Validation: Password ──────────────────────────────────────

    public function test_register_fails_without_password(): void
    {
        $payload = $this->validPayload();
        unset($payload['password'], $payload['password_confirmation']);

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    public function test_register_fails_with_empty_password(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'password' => '',
            'password_confirmation' => '',
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    public function test_register_fails_when_password_is_too_short(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'password' => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    public function test_register_succeeds_with_password_at_min_length(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'password' => 'exactly8',
            'password_confirmation' => 'exactly8',
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_register_fails_when_password_confirmation_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['password_confirmation']);

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    public function test_register_fails_when_password_confirmation_does_not_match(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'password' => 'password123',
            'password_confirmation' => 'different456',
        ]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('password');
    }

    // ─── Edge Cases ────────────────────────────────────────────────

    public function test_register_fails_with_empty_payload(): void
    {
        $response = $this->postJson(self::ENDPOINT, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['username', 'email', 'password']);
    }

    public function test_register_ignores_extra_fields(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload([
            'role' => 'admin',
            'is_admin' => true,
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_register_rejects_get_request(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function test_register_returns_json_content_type(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_register_token_can_access_protected_routes(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $token = $response->json('token.access_token');

        $meResponse = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer $token",
        ]);

        $meResponse->assertStatus(Response::HTTP_OK);
    }
}


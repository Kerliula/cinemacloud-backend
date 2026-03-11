<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    // ─── Full Lifecycle ────────────────────────────────────────────

    public function test_full_auth_lifecycle(): void
    {
        // 1. Register
        $registerResponse = $this->postJson('/api/auth/register', [
            'username' => 'lifecycle',
            'email' => 'lifecycle@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(Response::HTTP_CREATED);
        $registerToken = $registerResponse->json('token.access_token');

        // 2. Access profile with register token
        $meResponse = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$registerToken}",
        ]);

        $meResponse->assertSuccessful()
            ->assertJson([
                'user' => [
                    'username' => 'lifecycle',
                    'email' => 'lifecycle@example.com',
                ],
            ]);

        // 3. Logout
        $this->postJson('/api/auth/logout', [], [
            'Authorization' => "Bearer {$registerToken}",
        ])->assertStatus(Response::HTTP_NO_CONTENT);

        // 4. Old token is invalid
        $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$registerToken}",
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);

        // 5. Login with the registered credentials
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'lifecycle@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertSuccessful();
        $loginToken = $loginResponse->json('token.access_token');

        // 6. Refresh
        $refreshResponse = $this->postJson('/api/auth/refresh', [], [
            'Authorization' => "Bearer {$loginToken}",
        ]);

        $refreshResponse->assertStatus(Response::HTTP_OK);
        $refreshedToken = $refreshResponse->json('access_token');
        $this->assertNotEquals($loginToken, $refreshedToken);

        // 7. Use refreshed token
        $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$refreshedToken}",
        ])->assertSuccessful();

        // 8. Final logout
        $this->postJson('/api/auth/logout', [], [
            'Authorization' => "Bearer {$refreshedToken}",
        ])->assertStatus(Response::HTTP_NO_CONTENT);
    }

    // ─── Token Isolation ──────────────────────────────────────────

    public function test_tokens_are_isolated_between_users(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // User 1 token returns user 1 data
        $token1 = Auth::login($user1);
        $me1 = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$token1}",
        ]);
        $me1->assertJson(['user' => ['email' => 'user1@example.com']]);

        // Reset auth state
        Auth::logout();

        // User 2 token returns user 2 data
        $token2 = Auth::login($user2);
        $me2 = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$token2}",
        ]);
        $me2->assertJson(['user' => ['email' => 'user2@example.com']]);
    }

    // ─── Register Then Login ──────────────────────────────────────

    public function test_user_can_login_after_registration(): void
    {
        $this->postJson('/api/auth/register', [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(Response::HTTP_CREATED);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'new@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'user' => ['uuid', 'username', 'email', 'created_at'],
                'token' => ['access_token', 'token_type', 'expires_in'],
            ]);
    }

    // ─── Protected Routes ─────────────────────────────────────────

    public function test_all_protected_routes_require_authentication(): void
    {
        // Only routes behind auth:api middleware return 401 when unauthenticated.
        // The refresh route is behind throttle.auth (not auth:api), so it is
        // excluded from this assertion.
        $protectedRoutes = [
            ['POST', '/api/auth/logout'],
            ['GET', '/api/auth/me'],
        ];

        foreach ($protectedRoutes as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(
                Response::HTTP_UNAUTHORIZED,
                "{$method} {$uri} should require authentication",
            );
        }
    }

    // ─── Public Routes ────────────────────────────────────────────

    public function test_public_routes_are_accessible_without_auth(): void
    {
        // Login endpoint is accessible (will fail validation, not 401)
        $this->postJson('/api/auth/login', [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Register endpoint is accessible (will fail validation, not 401)
        $this->postJson('/api/auth/register', [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // ─── Concurrent Sessions ──────────────────────────────────────

    public function test_user_can_have_multiple_active_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'multi@example.com',
            'password' => bcrypt('password123'),
        ]);

        $login1 = $this->postJson('/api/auth/login', [
            'email' => 'multi@example.com',
            'password' => 'password123',
        ]);

        $login2 = $this->postJson('/api/auth/login', [
            'email' => 'multi@example.com',
            'password' => 'password123',
        ]);

        $token1 = $login1->json('token.access_token');
        $token2 = $login2->json('token.access_token');

        $this->assertNotEquals($token1, $token2);
    }
}

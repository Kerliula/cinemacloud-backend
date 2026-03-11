<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/auth/logout';

    // ─── Happy Path ────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_token_is_invalidated_after_logout(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $meResponse = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$token}",
        ]);

        $meResponse->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logout_response_has_empty_body(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getContent());
    }

    // ─── Unauthenticated ──────────────────────────────────────────

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson(self::ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logout_fails_with_invalid_token(): void
    {
        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logout_fails_with_empty_bearer(): void
    {
        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => 'Bearer ',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    // ─── Edge Cases ────────────────────────────────────────────────

    public function test_double_logout_fails(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(Response::HTTP_NO_CONTENT);

        $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logout_rejects_get_request(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RefreshTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/auth/refresh';

    // ─── Happy Path ────────────────────────────────────────────────

    public function test_authenticated_user_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);
    }

    public function test_refreshed_token_is_different_from_original(): void
    {
        $user = User::factory()->create();
        $originalToken = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$originalToken}",
        ]);

        $newToken = $response->json('access_token');
        $this->assertNotEquals($originalToken, $newToken);
    }

    public function test_refreshed_token_is_valid_jwt(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $newToken = $response->json('access_token');
        $this->assertCount(3, explode('.', $newToken));
    }

    public function test_new_token_works_after_refresh(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $newToken = $response->json('access_token');

        $meResponse = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$newToken}",
        ]);

        $meResponse->assertSuccessful();
    }

    public function test_refresh_returns_positive_expires_in(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertGreaterThan(0, $response->json('expires_in'));
    }

    public function test_refresh_response_has_no_user_section(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertArrayNotHasKey('user', $response->json());
    }

    // ─── Unauthenticated ──────────────────────────────────────────

    public function test_unauthenticated_user_cannot_refresh(): void
    {
        $response = $this->postJson(self::ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_refresh_fails_with_invalid_token(): void
    {
        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    // ─── Edge Cases ────────────────────────────────────────────────

    public function test_refresh_rejects_get_request(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function test_refresh_returns_json_content_type(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertHeader('Content-Type', 'application/json');
    }
}

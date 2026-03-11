<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/auth/me';

    // ─── Happy Path ────────────────────────────────────────────────

    public function test_authenticated_user_can_get_own_profile(): void
    {
        $user = User::factory()->create([
            'username' => 'johndoe',
            'email' => 'john@example.com',
        ]);
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'user' => [
                    'uuid',
                    'username',
                    'email',
                    'created_at',
                ],
            ])
            ->assertJson([
                'user' => [
                    'username' => 'johndoe',
                    'email' => 'john@example.com',
                ],
            ]);
    }

    public function test_me_returns_uuid(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertNotNull($response->json('user.uuid'));
    }

    public function test_me_returns_created_at(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertNotNull($response->json('user.created_at'));
    }

    public function test_me_does_not_expose_password(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertArrayNotHasKey('password', $response->json('user'));
        $this->assertArrayNotHasKey('remember_token', $response->json('user'));
    }

    public function test_me_does_not_include_token_section(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertSuccessful();
        $this->assertArrayNotHasKey('token', $response->json());
    }

    public function test_me_returns_correct_user_among_many(): void
    {
        User::factory()->create(['email' => 'other@example.com']);
        $user = User::factory()->create(['email' => 'target@example.com']);
        User::factory()->create(['email' => 'another@example.com']);

        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertJson([
            'user' => [
                'email' => 'target@example.com',
            ],
        ]);
    }

    // ─── Unauthenticated ──────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_me_fails_with_invalid_token(): void
    {
        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => 'Bearer invalid-token-here',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_me_fails_with_empty_bearer(): void
    {
        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => 'Bearer ',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_me_fails_after_logout(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $this->postJson('/api/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    // ─── Edge Cases ────────────────────────────────────────────────

    public function test_me_rejects_post_request(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->postJson(self::ENDPOINT, [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function test_me_returns_json_content_type(): void
    {
        $user = User::factory()->create();
        $token = Auth::login($user);

        $response = $this->getJson(self::ENDPOINT, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertHeader('Content-Type', 'application/json');
    }
}

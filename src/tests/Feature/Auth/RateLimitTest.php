<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/auth/login';

    public function test_rate_limit_blocks_after_max_attempts(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        $maxAttempts = (int) config('rate_limiting.auth.max_attempts', 5);

        // Fill up the rate-limiter by failing maxAttempts times (each 401 increments the counter)
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson(self::ENDPOINT, [
                'email' => 'user@example.com',
                'password' => 'wrongpassword',
            ])->assertStatus(Response::HTTP_UNAUTHORIZED);
        }

        // The next request must be blocked
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    public function test_rate_limit_response_contains_message(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        $maxAttempts = (int) config('rate_limiting.auth.max_attempts', 5);

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson(self::ENDPOINT, [
                'email' => 'user@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS)
            ->assertJsonStructure(['message']);
    }

    public function test_rate_limit_does_not_block_successful_login(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        // Fewer failed attempts than the limit — successful login must still work
        $response = $this->postJson(self::ENDPOINT, [
            'email' => 'user@example.com',
            'password' => 'correctpassword',
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\AuthResultDTO;
use App\DTOs\Auth\TokenDTO;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class RegisterResultDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_constructed(): void
    {
        $user = User::factory()->create();
        $token = new TokenDTO(accessToken: 'abc', tokenType: 'bearer', expiresIn: 3600);

        $dto = new AuthResultDTO(user: $user, token: $token);

        $this->assertSame($user, $dto->user);
        $this->assertSame($token, $dto->token);
    }

    public function test_to_array_returns_user_and_token(): void
    {
        $user = User::factory()->create();
        $token = new TokenDTO(accessToken: 'abc', tokenType: 'bearer', expiresIn: 3600);

        $dto = new AuthResultDTO(user: $user, token: $token);
        $array = $dto->toArray();

        $this->assertArrayHasKey('user', $array);
        $this->assertArrayHasKey('token', $array);
        $this->assertCount(2, $array);
    }

    public function test_is_readonly(): void
    {
        $reflection = new ReflectionClass(AuthResultDTO::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}

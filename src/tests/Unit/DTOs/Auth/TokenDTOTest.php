<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\TokenDTO;
use PHPUnit\Framework\TestCase;

class TokenDTOTest extends TestCase
{
    public function test_can_be_constructed(): void
    {
        $dto = new TokenDTO(
            accessToken: 'abc.def.ghi',
            tokenType: 'bearer',
            expiresIn: 3600,
        );

        $this->assertEquals('abc.def.ghi', $dto->accessToken);
        $this->assertEquals('bearer', $dto->tokenType);
        $this->assertEquals(3600, $dto->expiresIn);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new TokenDTO(
            accessToken: 'abc.def.ghi',
            tokenType: 'bearer',
            expiresIn: 3600,
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('access_token', $array);
        $this->assertArrayHasKey('token_type', $array);
        $this->assertArrayHasKey('expires_in', $array);
        $this->assertCount(3, $array);
    }

    public function test_to_array_returns_correct_values(): void
    {
        $dto = new TokenDTO(
            accessToken: 'my-token',
            tokenType: 'bearer',
            expiresIn: 7200,
        );

        $this->assertEquals([
            'access_token' => 'my-token',
            'token_type' => 'bearer',
            'expires_in' => 7200,
        ], $dto->toArray());
    }

    public function test_to_array_uses_snake_case_keys(): void
    {
        $dto = new TokenDTO(
            accessToken: 'token',
            tokenType: 'bearer',
            expiresIn: 3600,
        );

        $keys = array_keys($dto->toArray());

        $this->assertEquals(['access_token', 'token_type', 'expires_in'], $keys);
    }
}

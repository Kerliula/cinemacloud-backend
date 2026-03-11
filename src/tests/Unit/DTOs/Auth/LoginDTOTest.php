<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Http\Requests\Auth\LoginRequest;
use ReflectionClass;
use Tests\TestCase;

class LoginDTOTest extends TestCase
{
    public function test_can_be_constructed(): void
    {
        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('password', $array);
        $this->assertCount(2, $array);
    }

    public function test_to_array_returns_correct_values(): void
    {
        $dto = new LoginDTO(
            email: 'john@example.com',
            password: 'secret123',
        );

        $this->assertEquals([
            'email' => 'john@example.com',
            'password' => 'secret123',
        ], $dto->toArray());
    }

    public function test_is_readonly(): void
    {
        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $reflection = new ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_from_request_creates_dto(): void
    {
        $request = LoginRequest::create('/api/auth/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $request->setContainer($this->app);
        $request->validateResolved();

        $dto = LoginDTO::fromRequest($request);

        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function test_from_request_ignores_extra_fields(): void
    {
        $request = LoginRequest::create('/api/auth/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);
        $request->setContainer($this->app);
        $request->validateResolved();

        $dto = LoginDTO::fromRequest($request);

        $array = $dto->toArray();
        $this->assertArrayNotHasKey('remember', $array);
        $this->assertCount(2, $array);
    }

    public function test_to_array_does_not_contain_username(): void
    {
        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $this->assertArrayNotHasKey('username', $dto->toArray());
    }
}

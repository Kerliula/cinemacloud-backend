<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class RegisterDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_constructed(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $this->assertEquals('testuser', $dto->username);
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('username', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('password', $array);
        $this->assertCount(3, $array);
    }

    public function test_to_array_returns_correct_values(): void
    {
        $dto = new RegisterDTO(
            username: 'john',
            email: 'john@example.com',
            password: 'secret123',
        );

        $this->assertEquals([
            'username' => 'john',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ], $dto->toArray());
    }

    public function test_is_readonly(): void
    {
        $dto = new RegisterDTO(
            username: 'testuser',
            email: 'test@example.com',
            password: 'password123',
        );

        $reflection = new ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_from_request_creates_dto(): void
    {
        $request = RegisterRequest::create('/api/auth/register', 'POST', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $request->setContainer($this->app);
        $request->validateResolved();

        $dto = RegisterDTO::fromRequest($request);

        $this->assertEquals('testuser', $dto->username);
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function test_from_request_ignores_password_confirmation(): void
    {
        $request = RegisterRequest::create('/api/auth/register', 'POST', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $request->setContainer($this->app);
        $request->validateResolved();

        $dto = RegisterDTO::fromRequest($request);

        $array = $dto->toArray();
        $this->assertArrayNotHasKey('password_confirmation', $array);
        $this->assertCount(3, $array);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}

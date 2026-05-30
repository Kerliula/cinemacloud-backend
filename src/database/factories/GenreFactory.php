<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Genre>
 */
final class GenreFactory extends Factory
{
    protected static ?int $genreCounter = 0;

    public function definition(): array
    {
        self::$genreCounter++;
        $name = fake()->word() . ' ' . fake()->word() . ' ' . self::$genreCounter;

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}

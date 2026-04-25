<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
final class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'release_year' => fake()->year(),
            'thumbnail_url' => fake()->imageUrl(640, 360, 'movies'),
            'embed_url' => 'https://www.youtube.com/embed/' . fake()->regexify('[A-Za-z0-9_-]{11}'),
            'trailer_url' => 'https://www.youtube.com/watch?v=' . fake()->regexify('[A-Za-z0-9_-]{11}'),
        ];
    }
}

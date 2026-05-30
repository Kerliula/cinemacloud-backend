<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movie>
 */
final class MovieFactory extends Factory
{
    protected $model = Movie::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'description' => fake()->paragraph(),
            'release_year' => fake()->year(),
            'thumbnail_url' => fake()->imageUrl(640, 360, 'movies'),
        ];
    }
}

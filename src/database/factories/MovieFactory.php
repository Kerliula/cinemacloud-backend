<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Movie;
use App\Models\MovieEmbedUrl;
use App\Models\MovieTrailerUrl;
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

    public function configure(): self
    {
        return $this->afterCreating(function (Movie $movie): void {
            MovieEmbedUrl::factory(random_int(1, 2))
                ->forMovie($movie)
                ->create();

            MovieTrailerUrl::factory(random_int(1, 2))
                ->forMovie($movie)
                ->create();
        });
    }
}

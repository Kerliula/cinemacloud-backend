<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Movie;
use App\Models\MovieTrailerUrl;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovieTrailerUrl>
 */
final class MovieTrailerUrlFactory extends Factory
{
    protected $model = MovieTrailerUrl::class;

    public function definition(): array
    {
        return [
            'movie_id' => Movie::factory(),
            'url' => 'https://www.youtube.com/watch?v=' . fake()->unique()->regexify('[A-Za-z0-9_-]{11}'),
            'provider' => 'youtube',
        ];
    }

    public function forMovie(Movie $movie): self
    {
        return $this->state(fn (): array => [
            'movie_id' => $movie->id,
        ]);
    }
}

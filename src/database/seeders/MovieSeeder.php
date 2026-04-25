<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Seeder;

final class MovieSeeder extends Seeder
{
    public function run(): void
    {
        $genres = Genre::all();

        Movie::factory(20)->create()->each(function (Movie $movie) use ($genres): void {
            $movie->genres()->attach(
                $genres->random(rand(1, 3))->pluck('id')
            );
        });
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class GenreSeeder extends Seeder
{
    private const GENRES = [
        'Action',
        'Adventure',
        'Animation',
        'Comedy',
        'Crime',
        'Documentary',
        'Drama',
        'Fantasy',
        'Horror',
        'Mystery',
        'Romance',
        'Science Fiction',
        'Thriller',
        'Western',
    ];

    public function run(): void
    {
        foreach (self::GENRES as $name) {
            Genre::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}

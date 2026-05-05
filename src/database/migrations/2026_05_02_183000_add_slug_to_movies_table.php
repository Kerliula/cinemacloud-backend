<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('title');
        });

        $movies = DB::table('movies')
            ->select(['id', 'title'])
            ->orderBy('id')
            ->get();

        $usedSlugs = [];

        foreach ($movies as $movie) {
            $baseSlug = Str::slug((string)$movie->title);

            if ($baseSlug === '') {
                $baseSlug = 'movie';
            }

            $slug = $baseSlug;
            $counter = 2;

            while (isset($usedSlugs[$slug])) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            DB::table('movies')
                ->where('id', $movie->id)
                ->update(['slug' => $slug]);

            $usedSlugs[$slug] = true;
        }

        Schema::table('movies', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->dropUnique('movies_slug_unique');
            $table->dropColumn('slug');
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('genre_movie', function (Blueprint $table): void {
            $table->foreignId('movie_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('genre_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['movie_id', 'genre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genre_movie');
    }
};

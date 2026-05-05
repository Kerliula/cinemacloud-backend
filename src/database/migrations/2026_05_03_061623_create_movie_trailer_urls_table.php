<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('movie_trailer_urls', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->string('url')->unique();
            $table->string('provider');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_trailer_urls');
    }
};

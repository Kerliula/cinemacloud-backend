<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('title')->index();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('release_year')->index();
            $table->text('thumbnail_url')->nullable();
            $table->text('embed_url');
            $table->text('trailer_url')->nullable();

            $table->fullText('title');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->dropColumn(['embed_url', 'trailer_url']);
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->text('embed_url');
            $table->text('trailer_url')->nullable();
        });
    }
};

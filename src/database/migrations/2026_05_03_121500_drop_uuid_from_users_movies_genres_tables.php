<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $this->dropUuidFromTable('users');
        $this->dropUuidFromTable('movies');
        $this->dropUuidFromTable('genres');
    }

    public function down(): void
    {
        $this->addUuidToTable('users');
        $this->addUuidToTable('movies');
        $this->addUuidToTable('genres');
    }

    private function dropUuidFromTable(string $table): void
    {
        if (!Schema::hasColumn($table, 'uuid')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('uuid');
        });
    }

    private function addUuidToTable(string $table): void
    {
        if (Schema::hasColumn($table, 'uuid')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->uuid('uuid')->nullable()->unique()->after('id');
        });
    }
};

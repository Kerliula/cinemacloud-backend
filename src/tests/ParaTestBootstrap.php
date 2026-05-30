<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Artisan;

/**
 * ParaTest Bootstrap Hook
 *
 * This hook runs before and after each parallel test process to ensure
 * proper database setup and isolation between processes.
 */
class ParaTestBootstrap
{
    /**
     * Called before each parallel process starts
     */
    public static function setupDatabase(): void
    {
        // Get the token for this process if it exists
        $token = getenv('TEST_TOKEN');

        // Run migrations for this process
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--quiet' => true,
        ]);

        // Optional: Seed the database if needed
        // Uncomment if you want to seed test data in each process
        // Artisan::call('db:seed', [
        //     '--force' => true,
        //     '--quiet' => true,
        // ]);
    }

    /**
     * Called after each parallel process completes
     */
    public static function teardownDatabase(): void
    {
        // Optional: Clean up after tests if needed
        // Database transactions will be rolled back automatically
    }
}

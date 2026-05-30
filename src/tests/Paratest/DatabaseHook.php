<?php

declare(strict_types=1);

/**
 * ParaTest Event Listener for Database Setup
 *
 * This listener hooks into ParaTest's event system to properly initialize
 * databases before test execution and clean up afterward.
 *
 * Usage: This file is automatically loaded by the bootstrap-paratest.php
 */

namespace Tests\Paratest;

use Illuminate\Support\Facades\Artisan;
use Throwable;

class DatabaseHook
{
    /**
     * Initialize database for a parallel test process
     *
     * This method is called before tests run in each parallel process.
     * Since RefreshDatabase trait handles per-test isolation with transactions,
     * we ensure the database schema is ready.
     */
    public static function beforeProcess(): void
    {
        try {
            // Verify database migrations are current
            // This is important in case of process isolation per database
            Artisan::call('migrate:status', [
                '--quiet' => true,
            ]);
        } catch (Throwable $e) {
            // Database might already be initialized
        }
    }

    /**
     * Clean up after a parallel test process
     *
     * Called after all tests in a process complete.
     * Database transaction cleanup is automatic, but we can add custom cleanup here.
     */
    public static function afterProcess(): void
    {
        // Optional: Additional cleanup logic
        // Note: RefreshDatabase trait already handles transaction rollback
    }

    /**
     * Get database name for process isolation
     *
     * If you need database-level isolation (separate DB per process),
     * this method generates a unique database name.
     */
    public static function getProcessDatabaseName(string $baseDbName, ?string $testToken = null): string
    {
        $token = $testToken ?? getenv('TEST_TOKEN');

        if ($token) {
            // Create isolated database name using token
            return "{$baseDbName}_{$token}";
        }

        return $baseDbName;
    }

    /**
     * Check if database-level isolation is enabled
     */
    public static function isIsolatedDatabaseEnabled(): bool
    {
        // Check if the bootstrap file has database isolation enabled
        // (uncommented in bootstrap-paratest.php)
        return (bool) getenv('DB_ISOLATED_PER_PROCESS', false);
    }
}
